<?php
namespace App\Core;

class View
{
    private array $data = [];

    /**
     * assign() — Controller에서 View 변수 등록
     */
    public function assign(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * render() — View 렌더링
     */
    public function render(string $viewPath, array $data = []): void
    {
        // BaseController에서 미리 assign()된 데이터와 병합
        $merged = array_merge($this->data, $data);

        extract($merged);

        $filePath = __DIR__ . "/../../resources/views/" . $viewPath . ".php";

        if (!file_exists($filePath)) {
            throw new \Exception("View file not found: " . $filePath);
        }

        require $filePath;
    }
}
