<?php

namespace App\Controller\Api\V1;

use App\Controller\AbstractController;
use App\Service\SystemService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @Controller(prefix="/api/v1/system")
 */
class SystemController extends AbstractController
{
    /**
     * @Inject
     * @var SystemService
     */
    protected $systemService;
    
    /**
     * 获取统计数据
     * 
     * @RequestMapping(path="/statistics", methods={"GET"})
     */
    public function getStatistics(RequestInterface $request)
    {
        try {
            $params = $request->all();
            $statistics = $this->systemService->getStatistics($params);
            return $this->success($statistics);
        } catch (\Exception $e) {
            logger()->error('获取统计数据异常: ' . $e->getMessage());
            return $this->fail(500, '获取统计数据失败');
        }
    }
}