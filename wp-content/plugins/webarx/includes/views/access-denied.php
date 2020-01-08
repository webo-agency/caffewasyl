<?php
// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Access Denied</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    <style type="text/css">
        .container{ margin-top: 50px; width: 768px; }
        .grey > div{font-size: 18px; font-weight: 400; text-shadow: 0px 1px 1px #fff; color: #726f6f;}
        h4{ font-size: 20px; }
        .card-content > a:last-child{ margin-top: 20px; display: inline-block; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content grey lighten-4">
                        <div style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis; padding-right:30px;">Access Denied - <?php echo htmlentities('http' . (($_SERVER['SERVER_PORT'] == 443) ? 's://' : '://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], ENT_QUOTES); ?>
                            <div>
                                <a href="https://www.webarxsecurity.com" target="_blank"><img style="width:40px; position:absolute; top:10px; right: 10px;" src="data:image/png;base64,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" alt=""></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-content">
                        <h4>Error Code <?php echo $fid; ?></h4>
                        <p>This request has been blocked by <a href="https://www.webarxsecurity.com" target="_blank">WebARX</a> Web Application Firewall .</p>
                        <p>If you are a legitimate user, contact the administrator of the site with above error code if this message persists.</p>
                        <a href="<?php echo get_site_url(); ?>">Return To Homepage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>