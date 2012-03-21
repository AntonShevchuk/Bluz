<?

return function (\Bluz\View\View $view, $src = null, $rel = 'stylesheet') {
    if (null === $src) {
        echo $view->headLinkFiles;
    } else {
        $view->headLinkFiles .= '<link type="text/css" href="' . $view->baseUrl($src) . '" rel="' . $rel .'"/>'."\n";
    }
};