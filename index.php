<?php

require_once 'lib/boot.php';

use Photobooth\Service\ApplicationService;
use Photobooth\Service\AssetService;
use Photobooth\Service\ProcessService;
use Photobooth\Utility\PathUtility;

$assetService = AssetService::getInstance();

if (!$config['ui']['skip_welcome']) {
    if (!is_file(PathUtility::getAbsolutePath('welcome/.skip_welcome'))) {
        header('location: ' . PathUtility::getPublicPath('welcome'));
        exit();
    }
}

if ($config['chromaCapture']['enabled']) {
    header('location: ' . PathUtility::getPublicPath('chroma'));
    exit();
}

// Login / Authentication check
if (
    !$config['login']['enabled'] ||
    (!$config['protect']['localhost_index'] && (isset($_SERVER['SERVER_ADDR']) && $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'])) ||
    ((isset($_SESSION['auth']) && $_SESSION['auth'] === true) || !$config['protect']['index'])
) {
    $pageTitle = ApplicationService::getInstance()->getTitle();
    $photoswipe = true;
    $randomImage = false;
    $remoteBuzzer = true;
} else {
    header('location: ' . $config['protect']['index_redirect']);
    exit();
}

include PathUtility::getAbsolutePath('template/components/main.head.php');
?>

<body class="gallery-mode--overlay ">

<?php include PathUtility::getAbsolutePath('template/components/video.background.php'); ?>
<?php include PathUtility::getAbsolutePath('template/components/preview.php'); ?>

<?php if ($config['video']['enabled'] && $config['video']['animation']): ?>
    <div id="videoAnimation">
        <ul class="left">
            <?php for ($i = 1; $i <= 50; $i++) {
                print '<li class="reel-item"></li>';
            } ?>
        </ul>
        <ul class="right">
            <?php for ($i = 1; $i <= 50; $i++) {
                print '<li class="reel-item"></li>';
            } ?>
        </ul>
    </div>
<?php endif; ?>
<?php

include PathUtility::getAbsolutePath('template/components/stage.start.php');
if (!$config['ui']['selfie_mode']) {
    include PathUtility::getAbsolutePath('template/components/stage.loader.php');
    include PathUtility::getAbsolutePath('template/components/stage.results.php');
}

if ($config['gallery']['enabled']) {
    include PathUtility::getAbsolutePath('template/components/gallery.php');
}

if ($config['filters']['enabled'] && !$config['ui']['selfie_mode']) {
    include PathUtility::getAbsolutePath('template/components/filter.php');
}

include PathUtility::getAbsolutePath('template/components/main.footer.php');

if ($config['ui']['selfie_mode']) {
    echo '<script src="' . $assetService->getUrl('resources/js/selfie.js') . '"></script>';
}

if ($config['background']['type'] === 'slideshow') {
    echo '<script src="' . $assetService->getUrl('resources/js/backgroundSlideshow.js') . '"></script>';
}
?>

<script>
    window.onload = function() {
        // Add the click event listener to the document
        document.addEventListener('click', function(event) {
            if (event.button === 0 || event.leftClick) {
                document.getElementsByClassName("takePic")[0].dispatchEvent(new MouseEvent('click'))
            }
        });
    }
</script>

<!-- <script>
    function trackLongClick(element, duration = 500) {
        let isClicking = false;
        let clickStartTime;

        // Track mouse events
        element.addEventListener('mousedown', (e) => {
            if (!isClicking) {
                isClicking = true;
                clickStartTime = Date.now();
            } else {
                // If another click happens while holding, reset the timer
                isClicking = true;
                clickStartTime = Date.now();
            }

            let currentTime = Date.now();
            let durationTime = currentTime - clickStartTime;

            while (durationTime < 500) {
                currentTime = Date.now();
                durationTime = currentTime - clickStartTime
                continue;
            }
            
            console.log("LONG PRESSEROO!");
        });

        element.addEventListener('mouseup', (e) => {
            if (isClicking) {
                const currentTime = Date.now();
                const durationTime = currentTime - clickStartTime;

                if (durationTime > duration) {
                    alert(`Long click detected! Duration: ${durationTime}ms`);
                }

                isClicking = false;
            }
        });

        element.addEventListener('mouseleave', () => {
            // Click ends when mouse leaves the element
            isClicking = false;
        });
    }

    // Initialize the global handler
    document.addEventListener('DOMContentLoaded', () => {
        trackLongClick(document, 0);
    });
</script> -->

<script src="<?= $assetService->getUrl('resources/js/preview.js') ?>"></script>
<script src="<?= $assetService->getUrl('resources/js/virtualKeyboard.js') ?>"></script>
<script src="<?= $assetService->getUrl('resources/js/core.js') ?>"></script>

<?php include PathUtility::getAbsolutePath('template/components/start.adminshortcut.php'); ?>
<?php ProcessService::getInstance()->boot(); ?>
</body>
</html>
