<?

return function (\Bluz\View\View $view, $script = null) {
    if (null === $script) {
        echo $view->headScriptFiles;
        if ($view->headScriptContent) {
            ?>
            <script type="text/javascript">
            <!--
                <? echo $view->headScriptContent;?>
            //-->
            </script>
            <?
        }
    } elseif ('.js' == substr($script, -3, 3)) {
        $view->headScriptFiles .= '<script type="text/javascript" src="' . $view->baseUrl($script) .'"></script>'."\n";
    } else {
        $view->headScriptContent .= $script . ';';
    }
};