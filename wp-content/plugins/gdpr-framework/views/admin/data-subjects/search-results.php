<div>
    <h3>Results for: <?= esc_html($email); ?></h3>
    <?php if ($hasData): ?>

        <?php if (isset($links['profile'])): ?>
            <p>
                <strong><?= _x('Username', '(Admin)', 'gdpr-framework'); ?>:</strong>
                <a href="<?= $links['profile']; ?>"><?= esc_html($userName); ?></a>
            </p>
        <?php else: ?>
            <p>
                <em>
                    <?= _x('Data found.', '(Admin)', 'gdpr-framework'); ?>
                    <strong><?= esc_html($email); ?></strong> <?= _x('is not a registered user.', '(Admin)', 'gdpr-framework'); ?>
                </em>
            </p>
        <?php endif; ?>

        <hr>

        <a class="button button-primary" href="<?= esc_url($links['view']); ?>"><?= _x('Download data (html)', '(Admin)', 'gdpr-framework'); ?></a>
        <a class="button button-primary" href="<?= esc_url($links['export']); ?>"><?= _x('Export data (json)', '(Admin)', 'gdpr-framework'); ?></a>

        <?php if ($adminCap): ?>
            <p>
                <strong><?= _x('This user has admin capabilities. Deleting data via this interface is disabled.', '(Admin)', 'gdpr-framework'); ?></strong>
            </p>
        <?php else: ?>
            <a class="button button-primary" href="<?= esc_url($links['anonymize']); ?>"><?= _x('Anonymize data', '(Admin)', 'gdpr-framework'); ?></a>
            <a class="button button-primary" href="<?= esc_url($links['delete']); ?>"><?= _x('Delete data', '(Admin)', 'gdpr-framework'); ?></a>
        <?php endif; ?>

    <?php else: ?>
        <p><?= _x('No data found!', '(Admin)', 'gdpr-framework'); ?></p>
    <?php endif; ?>

    <hr>

    <?php if (count($consentData)): ?>
        <table class="gdpr-consent">
            <th colspan="2"><?= _x('Consents given', '(Admin)', 'gdpr-framework'); ?></th>
            <?php foreach ($consentData as $item): ?>
                <tr>
                    <td>
                        &#10004;
                    </td>
                    <td>
                        <?= $item['title']; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p><?= _x('No consents given!', '(Admin)', 'gdpr-framework'); ?>.</p>
    <?php endif; ?>

    <hr>
    <?php if($ClassiDocsdata){
    $ClassiDocsdata = json_decode($ClassiDocsdata);
    if(isset($ClassiDocsdata->documents->documents)){
        $documents = $ClassiDocsdata->documents->documents;
    }
    if(isset($documents)):
        ?>
        <table id="classiDocs_dataTable" class="display">
        <thead>
        <tr>
            <th></th>
            <th colspan="3"><?= __('Previous Results', 'gdpr-framework'); ?></th>
            <th colspan="3"><?= __('Current Results', 'gdpr-framework'); ?></th>
            <th colspan="1"></th>
        </tr>
        <tr>
            <th class="text-center"><input class="flat" onchange="" type="checkbox"></th>
            <th>
                <?= __('Name', 'gdpr-framework'); ?>
            </th>
            <th>
                <?= __('Path', 'gdpr-framework'); ?>
            </th>
            <th>
                <?= __('Workstation', 'gdpr-framework'); ?>
            </th>
            <th>
                <?= __('Name', 'gdpr-framework'); ?>
            </th>
            <th>
                <?= __('Path', 'gdpr-framework'); ?>
            </th>
            <th>
                <?= __('Workstation', 'gdpr-framework'); ?>
            </th>
            <th>
                <?= __('Last Scan', 'gdpr-framework'); ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($documents as $document):?>
            <tr>
                <th class="text-center">
                    <input name="documentIds" value="<?= $document->id;?>" type="checkbox">
                </th>
                <td><?= $document->prevName;?></td>
                <td><?= $document->prevFilePath;?></td>
                <td><?= $document->prevWorkstation;?></td>
                <td><?= $document->name;?></td>
                <td><?= $document->filePath;?></td>
                <td><?= $document->workstation;?></td>
                <td><?php 
                $originalDate = $document->lastScan;
                echo  $lastScan = date("m/d/Y H:i:s", strtotime($originalDate));
                ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        </table> 
    <?php else: ?>
        <p><?= _x('No ClassiDocs data found!', '(Admin)', 'gdpr-framework'); ?>.</p>
    <?php endif; ?>
    

</div>
<br>
<hr>
<?php }?>
