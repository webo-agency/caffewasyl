<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

ini_set('max_execution_time', 60 * 50);

/**
 * This class is used to provide the backup functionality.
 */
class W_Backup extends W_Core
{
    /**
     * Google API client object.
     *
     * @var Google_Client
     */
    private $googleClient = null;

    /**
     * Google API service object.
     *
     * @var Google_Service_Drive
     */
    private $googleService = null;

    /**
     * Backup interval in seconds.
     *
     * @var integer
     */
    protected $interval;

    /**
     * Whether or not we need to take a backup.
     *
     * @var boolean
     */
    protected $needBackup;

	/**
	 * Add the actions required to perform backups.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
        parent::__construct($core);

        // The backup feature can only be used on an activated license.
        if (!$this->license_is_active()) {
            return;
        }

        // Check if we need to schedule a backup
        $this->interval = $this->getInterval(get_site_option('webarx_backup_frequency'));
        $this->needBackup = (int) time() > ((int) get_site_option('webarx_last_backup_timestamp', 0) + (int) $this->interval);

        // Register the actions.
        add_action('init', array($this, 'scheduleBackup'));
        add_action('webarx_create_backup', array($this, 'zipAllFiles'));
        add_action('webarx_revertFiles', array($this, 'revertFiles'), 10, 1);
    }

    /**
     * Convert the interval in text to a number.
     * 
     * @param string $interval
     * @return integer
     */
    public function getInterval($interval)
    {
        switch($interval) {
            case '12hours':
                return 43200;
                break;
            case '24hours':
                return 86400;
                break;
            case '48hours':
                return 172800;
                break;
            case 'week':
                return 604800;
                break;
            default:
                return 86400;
                break;
        }
    }

    /**
     * Initialize Google API Client.
     * 
     * @param $fresh Should we re-initialize it regardless of if it's already set?
     * @return void
     */
    public function initGoogleClient($fresh = false)
    {
        if (get_site_option('webarx_googledrive_access_token') && ($fresh || $this->googleClient == null || $this->googleService == null)) {

            // Attempt to initialize the Google Client and Drive Service.
            try {
                require_once __DIR__ . '/../lib/google-api-php-client/vendor/autoload.php';
                $this->googleClient = new Google_Client();
                $this->googleClient->setClientId('306900124313-oeblp5fv9hp4eak9ec4aqnleajtcim0g.apps.googleusercontent.com');
                $this->googleClient->setAccessType('offline');
                $this->googleClient->setAccessToken(get_site_option('webarx_googledrive_access_token'));
                $this->googleService = new Google_Service_Drive($this->googleClient);
            } catch (\Exception $exception) {
                // Nothing for now.
            }
        }
    }

    /**
     * Generate a random string.
     * 
     * @param integer $length
     * @return string
     */
    public function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))),1,$length);
    }

    /**
     * Schedule the event to create a backup, but only if the access token is set.
     * 
     * @return void
     */
    public function scheduleBackup()
    {
        if (get_site_option('webarx_googledrive_access_token') != '' && !wp_next_scheduled('webarx_create_backup') && $this->needBackup) {
            $this->checkPreviousBackups();
            wp_schedule_event(time(), 'webarx_now', 'webarx_create_backup');
        }
    }

    /**
     * Get file count.
     * 
     * @param string $path
     * @return integer 
     */
    private function getFileCount($path)
    {
        $size = 0;
        $ignore = array('.', '..', 'cgi-bin', '.DS_Store');
        $files = scandir($path);
        foreach ($files as $t) {
            if (in_array($t, $ignore)) {
                continue;
            }

            if (is_dir(rtrim($path, '/') . '/' . $t)) {
                $size += $this->getFileCount(rtrim($path, '/') . '/' . $t);
            } else {
                $size++;
            }
        }
        return $size;
    }

    /**
     * Zip all files
     *
     * @param bool $requested
     * @throws Exception
     */
    public function zipAllFiles($requested = false)
    {
        if (get_site_option('webarx_googledrive_backup_is_running', false) == false && ($this->needBackup || $requested == true)) {

            // Delete old backup files if they were not deleted at the end of the previous backup.
            $backupTemp = get_site_option('webarx_archive_temp_filename', 'backup');
            $dumpTemp = get_site_option('webarx_dump_temp_filename', 'dump');
            $filesTemp = get_site_option('webarx_files_temp_filename', 'files');
            wp_delete_file($this->plugin->path . '/data/' . $backupTemp . '.zip');
            wp_delete_file($this->plugin->path . '/data/' . $dumpTemp . '.sql');
            wp_delete_file($this->plugin->path . '/data/' . $filesTemp . '.txt');

            // Log timestamp when last backup completed.
            update_site_option('webarx_last_backup_timestamp', time());

            // Generate a random name for the temporary backup files.
            $archiveTemp = $this->generateRandomString();
            $filesTemp = $this->generateRandomString();
            $dumpTemp = $this->generateRandomString();
            update_site_option('webarx_archive_temp_filename', $archiveTemp);
            update_site_option('webarx_files_temp_filename', $filesTemp);
            update_site_option('webarx_dump_temp_filename', $dumpTemp);

            // Obtain the access token.
            wp_unschedule_hook('webarx_create_backup');
            $this->fetchNewToken();
            update_site_option('webarx_googledrive_backup_is_running', true);
            update_site_option('webarx_googledrive_upload_state', 'Creating backup ZIP');

            // Start zipping files.
            @ini_set('memory_limit', '2048M');
            @set_time_limit(900);
            ob_start();

            $path = ABSPATH;
            $zip = new ZipArchive();
            $zip->open($this->plugin->path . '/data/' . $archiveTemp . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

            // Loop through all the files and start adding it to the archive.
            $currentFileCount = 0;
            $totalFileCount = $this->getFileCount($path);
            foreach ($files as $name => $file) {
                if ($file->isDir()) {
                    flush();
                    continue;
                }

                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($path));
                $zip->addFile($filePath, $relativePath);
                $currentFileCount++;

                // Only update the current state every 250 files.
                if ($currentFileCount % 250 == 0) {
                    update_site_option('webarx_googledrive_upload_state', 'Creating backup ' . $currentFileCount . '/' . $totalFileCount);
                }
            }
            update_site_option('webarx_googledrive_upload_state', 'Creating backup ' . $currentFileCount . '/' . $totalFileCount);
            ob_end_flush();
            $zip->close();

            // Now upload the backup file.
            $this->uploadBackup();
            update_site_option('webarx_googledrive_backup_is_running', false);

            // Delete the temporary backup files.
            wp_delete_file($this->plugin->path . '/data/' . $archiveTemp . '.zip');
            wp_delete_file($this->plugin->path . '/data/' . $dumpTemp . '.sql');
            wp_delete_file($this->plugin->path . '/data/' . $filesTemp . '.txt');
            wp_die();
        }
    }

    /**
     * Check number of backup copies in the Google Cloud and delete old if needed.
     * 
     * @return void
     */
    private function checkPreviousBackups()
    {
        require_once __DIR__ . '/../lib/google-api-php-client/vendor/autoload.php';
        $this->initGoogleClient();
        $this->fetchNewToken();

        // Get the old backup files.
        $siteFolderId = get_site_option('webarx_googledrive_site_folder');
        try {
            $backups = $this->googleService->files->listFiles(array('q' => "'" . $siteFolderId . "' in parents"));
        } catch (\Exception $exception) {
            update_site_option('webarx_googledrive_upload_state', 'Failed retrieving old backup list.');
            return;
        }
        
        $filesCount = count($backups->files);

        // Don't do anything if there are no files.
        if ($filesCount <= 0) {
            return;
        }

        // Now start deleting files.
        while ($filesCount >= get_site_option('webarx_backups_limit', 7)) {
            // Attempt to delete the file. Don't do anything with exceptions.
            try {
                $this->googleService->files->delete(end($backups->files)->id);
            } catch (\Exception $e) {
                // Nothing for now.
            }
            $filesCount = $filesCount - 1;
        }
    }

    /**
     * Get chunk.
     * 
     * @param $handle
     * @param integer $chunkSize
     * @return mixed
     */
    private function readFileChunk($handle, $chunkSize)
    {
        $byteCount = 0;
        $giantChunk = '';

        while (!@feof($handle)) {
            // fread will never return more than 3Mb if the stream is read buffered and it does not represent a plain file
            $chunk = fread($handle, 1 * 1024 * 1024);
            $byteCount += strlen($chunk);
            $giantChunk .= $chunk;

            if ($byteCount >= $chunkSize) {
                return $giantChunk;
            }
        }

        return $giantChunk;
    }

    /**
     * Get files scope.
     * 
     * @return void
     */
    public function scopeFiles()
    {
        $this->initGoogleClient();

        // Loop through the files that should be part of the backup.
        $filesTemp = get_site_option('webarx_files_temp_filename', 'files');
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ABSPATH));
        $fh = fopen($this->plugin->path . '/data/' . $filesTemp . '.txt', 'a');
        if (!$fh) {
            return;
        }

        // Loop
        foreach ($files as $name => $file) {
            if ($file->isDir()) {
                flush();
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen(ABSPATH));
            fwrite($fh, $filePath . "\n");
        }

        // Create a file that contains all files to be cloned.
        try {
            require_once __DIR__ . '/../lib/google-api-php-client/vendor/autoload.php';
            $file = new Google_Service_Drive_DriveFile();
            $file->setName('files.txt');
            $file->setParents([get_site_option('webarx_googledrive_backup_temp_name')]);
            $chunkSizeBytes = 1 * 1024 * 1024;
    
            // Call the API with the media upload, defer so it doesn't immediately return.
            $this->googleClient->setDefer(true);
            $request = $this->googleService->files->create($file);
    
            // Create a media file upload to represent our upload process.
            $media = new Google_Http_MediaFileUpload($this->googleClient, $request, 'text/plain', null, true, $chunkSizeBytes);
            $filesize = filesize($this->plugin->path . '/data/' . $filesTemp . '.txt');
            $media->setFileSize($filesize);
        } catch (\Exception $exception) {
            update_site_option('webarx_googledrive_upload_state', 'Failed uploading file list to Google Drive.');
            return;
        }

        // Upload the various chunks. $status will be false until the process is complete.
        $status = false;
        $handle = fopen($this->plugin->path . '/data/' . $filesTemp . '.txt', "rb");
        $uploadedFileSize = 0;
        while (!$status && !@feof($handle)) {
            $chunk = $this->readFileChunk($handle, $chunkSizeBytes);
            $uploadedFileSize += $chunkSizeBytes;
            $status = $media->nextChunk($chunk);
        }

        update_site_option('webarx_googledrive_upload_state', $uploadedFileSize  . '/' . $filesize);

        // The final value of $status will be the data from the API for the object
        // that has been uploaded.
        $result = false;
        if ($status != false) {
            $result = $status;
        }
        fclose($handle);

        // Delete the temporary files.
        update_site_option('webarx_googledrive_upload_state', 'Files scope uploaded...');
        wp_delete_file($this->plugin->path . '/data/'. $filesTemp . '.txt');
    }

    /**
     * Upload backup files to Google Ddrive.
     * 
     * @return void
     */
    public function uploadBackup()
    {
        require_once __DIR__ . '/../lib/google-api-php-client/vendor/autoload.php';
        $this->initGoogleClient();
        $archiveTemp = get_site_option('webarx_archive_temp_filename', 'archive');
        $dumpTemp = get_site_option('webarx_dump_temp_filename', 'dump');

        // Refresh access token before long lasting process
        $this->fetchNewToken();
        update_site_option('webarx_googledrive_upload_state', 'Starts uploading backup...');

        // Create backup folder for given time
        $d = new \DateTime();

        // Output the date with microseconds.
        $backupDirName = $d->format('Y-m-d\TH:i:s'); // 2011-01-01T15:03:01.012345
        update_site_option('webarx_googledrive_upload_state', 'Creating backup folder...');

        // Create new directory under that site folder for backup at that given time
        try {
            $file = new Google_Service_Drive_DriveFile();
            $file->setName($backupDirName);
            $file->setDescription('WebARX Backup at ' . $backupDirName);
            $file->setMimeType('application/vnd.google-apps.folder');
            $file->setParents([get_site_option('webarx_googledrive_site_folder')]);
            $backupDir = $this->googleService->files->create($file, array(
                'mimeType' => 'application/vnd.google-apps.folder',
                'uploadType' => 'multipart'
            ));
        } catch (\Exception $exception) {
            update_site_option('webarx_googledrive_upload_state', 'Failed creating backup folder on Google Drive.');
            return;
        }

        update_site_option('webarx_googledrive_upload_state', 'Created backup folder');

        // Set temporary backup directory ID
        update_site_option('webarx_googledrive_backup_temp_name', $backupDir->id);
        update_site_option('webarx_googledrive_upload_state', 'Starting backup upload...');

        // Define file backup .zip name
        $uploadFile = $this->plugin->path . '/data/' . $archiveTemp . '.zip';

        // Upload files backup
        $file = new Google_Service_Drive_DriveFile();
        $file->setName('files.zip');
        $file->setParents([$backupDir->id]);
        $chunkSizeBytes = 1 * 1024 * 1024;

        // Call the API with the media upload, defer so it doesn't immediately return.
        $this->googleClient->setDefer(true);
        $request = $this->googleService->files->create($file);

        // Create a media file upload to represent our upload process.
        $media = new Google_Http_MediaFileUpload($this->googleClient, $request, 'application/zip', null, true, $chunkSizeBytes);
        $filesize = filesize($uploadFile);
        $media->setFileSize($filesize);

        // Upload the various chunks. $status will be false until the process is complete.
        $status = false;
        $handle = fopen($uploadFile, 'rb');
        $uploadedFileSize = 0;

        $chunkCount = 0;
        while (!$status && !@feof($handle)) {
            $chunkCount = $chunkCount + 1;
            if ($chunkCount >= 10) {
                $this->fetchNewToken();
                $chunkCount = 0;
            }

            $chunk = $this->readFileChunk($handle, $chunkSizeBytes);
            $uploadedFileSize += $chunkSizeBytes;
            update_site_option('webarx_googledrive_upload_state', 'Uploading backup: ' .  round($uploadedFileSize / 1048576, 2)  . '/' . round($filesize / 1048576, 2) . ' Mb');
            $status = $media->nextChunk($chunk);
        }
        update_site_option('webarx_googledrive_upload_state', 'Finished backup upload');

        // The final value of $status will be the data from the API for the object
        // that has been uploaded.
        $result = false;
        if ($status != false) {
            $result = $status;
        }
        fclose($handle);

        $this->backupDatabase();
        $this->scopeFiles();
        update_site_option('webarx_googledrive_upload_state', 'Backup at ' . $backupDirName . ' completed');
        update_site_option('webarx_googledrive_backup_is_running', 0);

        // Delete the temporary files.
        wp_delete_file($this->plugin->path . '/data/' . $archiveTemp . '.zip');
        wp_delete_file($this->plugin->path . '/data/' . $dumpTemp . '.sql');
    }

    /**
     * Backup the database.
     * 
     * @return void
     */
    public function backupDatabase()
    {
        require_once __DIR__ . '/../lib/google-api-php-client/vendor/autoload.php';
        $this->initGoogleClient();

        // Set the backup stage.
        $dumpTemp = get_site_option('webarx_dump_temp_filename', 'dump');
        update_site_option('webarx_googledrive_upload_state', '');
        update_site_option('webarx_googledrive_backup_is_running', 0);
        wp_unschedule_hook('webarx_revertFiles');
        wp_unschedule_hook('webarx_create_backup');

        // Attempt to dump the entire database.
        require $this->plugin->path . 'lib/mysqldump-php/src/Ifsnop/Mysqldump/Mysqldump.php';
        try {
            $dump = new \Ifsnop\Mysqldump\Mysqldump('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
            $dump->start($this->plugin->path . '/data/' . $dumpTemp . '.sql');
        } catch (\Exception $e) {
            return;
        }

        update_site_option('webarx_googledrive_upload_state', 'Created database backup, uploading...');

        // Refresh access token before long lasting process
        $this->fetchNewToken();

        // Upload files backup
        $file = new Google_Service_Drive_DriveFile();
        $file->setName('mysql.sql');
        $file->setParents([get_site_option('webarx_googledrive_backup_temp_name')]);
        $chunkSizeBytes = 1 * 1024 * 1024;

        // Call the API with the media upload, defer so it doesn't immediately return.
        $this->googleClient->setDefer(true);
        $request = $this->googleService->files->create($file);

        // Create a media file upload to represent our upload process.
        $media = new Google_Http_MediaFileUpload($this->googleClient, $request, 'text/plain', null, true, $chunkSizeBytes);
        $filesize = filesize($this->plugin->path . '/data/' . $dumpTemp . '.sql');
        $media->setFileSize($filesize);

        // Upload the various chunks. $status will be false until the process is complete.
        $status = false;
        $handle = fopen($this->plugin->path . '/data/' . $dumpTemp . '.sql', 'rb');
        $uploadedFileSize = 0;
        while (!$status && !@feof($handle)) {
            $chunk = $this->readFileChunk($handle, $chunkSizeBytes);
            $uploadedFileSize += $chunkSizeBytes;
            $status = $media->nextChunk($chunk);
        }

        update_site_option('webarx_googledrive_upload_state', $uploadedFileSize  . '/' . $filesize);

        // The final value of $status will be the data from the API for the object
        // that has been uploaded.
        $result = false;
        if ($status != false) {
            $result = $status;
        }
        fclose($handle);
    }

    /**
     * Get all available backup files.
     * 
     * @return object
     */
    public function getAvailableBackups()
    {
        require_once __DIR__ . '/../lib/google-api-php-client/vendor/autoload.php';
        $this->initGoogleClient();

        try {
            return $this->googleService->files->listFiles(array('q' => "'" . get_site_option('webarx_googledrive_site_folder') . "' in parents"));
        } catch (\Exception $exception) {
            $this->fetchNewToken();
            $this->initGoogleClient(true);
            return $this->googleService->files->listFiles(array('q' => "'" . get_site_option('webarx_googledrive_site_folder') . "' in parents"));
        }
    }

    /**
     * Revert files.
     * 
     * @param string $backupId
     * @return void
     */
    public function revertFiles($backupId)
    {
        require_once __DIR__ . '/../lib/google-api-php-client/vendor/autoload.php';
        $this->initGoogleClient();

        if (get_site_option('webarx_googledrive_backup_is_running', true) != true) {
            wp_unschedule_hook('webarx_revertFiles');
            update_site_option('webarx_googledrive_backup_is_running', true);
            update_site_option('webarx_googledrive_upload_state', 'Downloading backup...');

            $backups = $this->googleService->files->listFiles(array('q' => "'" . $backupId . "' in parents", 'fields' => 'files(id, name, size)'));

            $files = '';
            $database = '';
            $filesList = '';
            foreach ($backups as $backup) {
                if ($backup->name == 'files.zip') {
                    $files = $backup;
                }
                if ($backup->name == 'mysql.sql') {
                    $database = $backup;
                }

                if ($backup->name == 'files.txt') {
                    $filesList = $backup;
                }
            }

            $fileId = $files->id;
            $fileSize = intval($files->size);

            // Get the authorized Guzzle HTTP client
            $http = $this->googleClient->authorize();

            // Open a file for writing
            $backupPath = get_site_option('webarx_archive_temp_filename', 'backup');
            $fp = fopen($this->plugin->path . '/data/' . $backupPath . '.zip', 'w');

            // Download in 1 MB chunks
            $chunkSizeBytes = 5 * 1024 * 1024;
            $chunkStart = 0;

            // Iterate over each chunk and write it to our file
            while ($chunkStart < $fileSize) {
                $chunkEnd = $chunkStart + $chunkSizeBytes;
                $response = $http->request('GET', sprintf('/drive/v3/files/%s', $fileId), ['query' => ['alt' => 'media'], 'headers' => ['Range' => sprintf('bytes=%s-%s', $chunkStart, $chunkEnd)]]);
                $chunkStart = $chunkEnd + 1;
                fwrite($fp, $response->getBody()->getContents());
                update_site_option('webarx_googledrive_upload_state', 'Downloading ' .  $chunkStart  . '/' . $fileSize . ' bytes');
            }

            update_site_option('webarx_googledrive_upload_state', 'Backup downloaded, replacing files...');

            // close the file pointer
            fclose($fp);

            // Delete files that doesnt exist
            $this->deleteOldFiles($filesList);

            // Revert the database
            $this->revertDatabase($database);
            $backupTemp = get_site_option('webarx_archive_temp_filename', 'backup');
            $dumpTemp = get_site_option('webarx_dump_temp_filename', 'dump');
            $filesTemp = get_site_option('webarx_files_temp_filename', 'files');

            // Delete temporary files.
            wp_delete_file($this->plugin->path . '/data/' . $backupTemp . '.zip');
            wp_delete_file($this->plugin->path . '/data/' . $dumpTemp . '.sql');
            wp_delete_file($this->plugin->path . '/data/' . $filesTemp . '.txt');
        }
    }

    /**
     * Delete old files.
     * 
     * @param object $files
     * @return void
     */
    public function deleteOldFiles($files)
    {
        require_once __DIR__ . '/../lib/google-api-php-client/vendor/autoload.php';
        $this->initGoogleClient();

        // Load filesystem
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();

        // Unzip the temporary zip file.
        $backupTemp = get_site_option('webarx_archive_temp_filename', 'backup');
        $unzipfile = unzip_file($this->plugin->path . '/data/' . $backupTemp . '.zip', ABSPATH);
        if (is_wp_error($unzipfile)) {
            update_site_option('webarx_googledrive_upload_state', 'There was an error in replacing files, please download backup from Google Drive and unzip files manually.');
            wp_die();
        } else {
            update_site_option('webarx_googledrive_upload_state', 'Successfully replaced old files');
        }

        $fileId = $files->id;
        $fileSize = intval($files->size);

        // Get the authorized Guzzle HTTP client
        $http = $this->googleClient->authorize();

        // Open a file for writing
        // Download in 1 MB chunks
        $chunkSizeBytes = 1 * 1024 * 1024;
        $chunkStart = 0;

        // Open a file for writing
        $filesTemp = get_site_option('webarx_files_temp_filename', 'files');
        $fp = fopen($this->plugin->path . '/data/' . $filesTemp . '.txt', 'w');

        // Iterate over each chunk and write it to our file
        while ($chunkStart < $fileSize) {
            $chunkEnd = $chunkStart + $chunkSizeBytes;
            $response = $http->request(
                'GET',
                sprintf('/drive/v3/files/%s', $fileId),
                [
                    'query' => ['alt' => 'media'],
                    'headers' => [
                        'Range' => sprintf('bytes=%s-%s', $chunkStart, $chunkEnd)
                    ]
                ]
            );
            $chunkStart = $chunkEnd + 1;
            fwrite($fp, $response->getBody()->getContents());

        }

        fclose($fp);

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ABSPATH));
        $fileCount = 0;
        $totalFileCount = $this->getFileCount(ABSPATH);

        $dumpTemp = get_site_option('webarx_dump_temp_filename', 'dump');
        foreach ($files as $name => $file) {
            $fileCount++;
            if ($file->isDir()) {
                flush();
                continue;
            }

            $filePath = $file->getRealPath();
            $handle = fopen($this->plugin->path . '/data/' . $filesTemp . '.txt', 'r');
            $found = false;
            while (($buffer = fgets($handle)) !== false) {
                if (strpos($buffer, $filePath) !== false) {
                    $found = true;
                    break;
                }
            }

            if ($found == false && $filePath != $this->plugin->path . '/data/' . $filesTemp . '.txt'
                && $filePath != $this->plugin->path . '/data/' . $backupTemp . '.zip'
                && $filePath != $this->plugin->path . '/data/' . $dumpTemp . '.sql'
            ) {
                wp_delete_file($filePath);
            }

            fclose($handle);
            update_site_option('webarx_googledrive_upload_state', 'Checking files: ' . $fileCount . '/' . $totalFileCount);
        }
    }

    /**
     * Revert a database.
     * 
     * @param object $files
     * @return void
     */
    public function revertDatabase($files)
    {
        require_once __DIR__ . '/../lib/google-api-php-client/vendor/autoload.php';
        $this->initGoogleClient();

        $fileId = $files->id;
        $fileSize = intval($files->size);

        // Get the authorized Guzzle HTTP client
        $http = $this->googleClient->authorize();

        // Open a file for writing
        // Download in 1 MB chunks
        $chunkSizeBytes = 1 * 1024 * 1024;
        $chunkStart = 0;

        // Open a file for writing
        $dumpTemp = get_site_option('webarx_dump_temp_filename', 'dump');
        $fp = fopen($this->plugin->path . '/data/' . $dumpTemp . '.sql', 'w');

        // Iterate over each chunk and write it to our file
        while ($chunkStart < $fileSize) {
            $chunkEnd = $chunkStart + $chunkSizeBytes;
            $response = $http->request('GET', sprintf('/drive/v3/files/%s', $fileId), ['query' => ['alt' => 'media'], 'headers' => ['Range' => sprintf('bytes=%s-%s', $chunkStart, $chunkEnd)]]);
            $chunkStart = $chunkEnd + 1;
            fwrite($fp, $response->getBody()->getContents());
        }
        fclose($fp);

        // Import the old database.
        global $wpdb;
        $sql = "SHOW TABLES";
        $results = $wpdb->get_results($sql);
        foreach ($results as $index => $value) {
            foreach ($value as $tableName) {
                $sql = "DROP TABLE IF EXISTS $tableName";
                $wpdb->query($sql);
            }
        }

        // Temporary variable, used to store current query
        $templine = '';

        // Read in entire file
        $lines = file($this->plugin->path . '/data/' . $dumpTemp . '.sql');

        // Loop through each line
        foreach ($lines as $line) {

            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || $line == '') {
                continue;
            }

            // Add this line to the current segment
            $templine .= $line;

            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';') {
                $wpdb->query($templine);
                $templine = '';
            }
        }

        update_site_option('webarx_googledrive_upload_state', 'Backup process completed');
        update_site_option('webarx_googledrive_backup_is_running', false);
    }

    /**
     * Remove directory recursively.
     * 
     * @param string $dir
     * @return void
     */
    public function rrmdir($dir)
    {
        $backupTemp = get_site_option('webarx_archive_temp_filename', 'backup');
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dir . '/' . $object)) {
                        $this->rrmdir($dir . '/' . $object);
                    } else {
                        if ($object != $backupTemp .  '.zip') {
                            unlink($dir . '/' . $object);
                        }
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Fetch the access / refresh token.
     * 
     * @return void
     */
    public function fetchNewToken()
    {
        $response = $this->plugin->api->send_request('/api/plugin/backup/google-drive/refresh', 'POST', array(
            'access_token' => base64_encode(get_site_option('webarx_googledrive_access_token')),
            'refresh_token' => base64_encode(get_site_option('webarx_googledrive_refresh_token'))
        ));

        if (isset($response['access_token']) && !empty($response['access_token'])) {
            update_site_option('webarx_googledrive_access_token', $response['access_token']);
        }

        if (isset($response['access_token']) && !empty($response['access_token'])) {
            update_site_option('webarx_googledrive_refresh_token', $response['refresh_token']);
        }
    }
}
