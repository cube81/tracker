<?php

namespace App\Core;

class View {
    private string $view;
    private array $data = [];

    public function __construct(string $view, array $data = []) {
        $this->view = $view;
        $this->data = $data;
    }

    public static function make(string $view, array $data = []): self {
        return new self($view, $data);
    }

    public function render(): string {
        extract($this->data);
        ob_start();
        include __DIR__ . '/../../src/Views/' . str_replace('.', '/', $this->view) . '.php';
        $content = ob_get_clean();

        // If not on login/reset page, wrap in layout
        if (strpos($this->view, 'auth.') !== 0) {
            ob_start();
            include __DIR__ . '/../../src/Views/layout/base.php';
            return ob_get_clean();
        }

        return $content;
    }

    public function __toString(): string {
        return $this->render();
    }
}
