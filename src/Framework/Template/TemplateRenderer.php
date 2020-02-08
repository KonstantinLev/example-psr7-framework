<?php

namespace Framework\Template;

class TemplateRenderer
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function render($view, array $params = []): string
    {
        $_templateFile_ = $this->path . '/' . $view . '.php';

        ob_start();
        extract($params, EXTR_OVERWRITE);
        require $_templateFile_;
        return ob_get_clean();
    }
}