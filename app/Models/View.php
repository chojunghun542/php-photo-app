<?php

namespace app\Core;

class View
{
    // View 클래스에 생성자를 명시적으로 추가하여 
    // 클래스가 제대로 정의되었음을 보장하고, 
    // BaseController가 인스턴스를 만들 때 문제 없도록 합니다.
    public function __construct()
    {
        // View가 특별히 초기화할 것이 없다면 비워둡니다.
    }

    /**
     * View 템플릿 파일을 불러와 사용자에게 출력합니다.
     * **static 키워드를 제거했습니다.**
     * BaseController가 $this->view->render() 형태로 호출할 수 있게 됩니다.
     * @param string $viewPath resources/views/ 이후의 경로 (예: 'user/register')
     * @param array $data View에 전달할 데이터
     */
    public function render(string $viewPath, array $data = []) // 👈 static 키워드 제거
    {
        // ... (나머지 코드는 동일) ...
        extract($data); 
        
        $filePath = __DIR__ . "/../../resources/views/" . $viewPath . ".php";

        if (!file_exists($filePath)) {
            throw new \Exception("View file not found: " . $filePath);
        }

        require $filePath;
    }
}
