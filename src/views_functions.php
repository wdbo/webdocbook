<?php

function _render_tpl($view, array $args = array())
{
    return _render(
        \App\FrontController::getInstance()->getTemplate($view),
        $args
    );
}

// Endfile
