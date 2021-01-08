<?php
namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Log;
use think\facade\Request;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            //return response($e->getMessage(), $e->getStatusCode());
            Log::error($e->getMessage().'，文件名：'.$e->getFile().'行号：'.$e->getTraceAsString());
            return api_res(-10000,'请求异常，请稍后再试~,错误原因：'.$e->getMessage());
        }

        //TODO::开发者对异常的操作
        //可以在此交由系统处理
        Log::error($e->getMessage().'，文件名：'.$e->getFile().'行号：'.$e->getTraceAsString());
        return api_res(-10000,'系统异常，请稍后再试~，错误原因：'.$e->getMessage());
    }

    /**
     * @param Throwable $exception
     * @return Response
     * Description:后台错误处理
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 10:10
     */
    protected function convertExceptionToResponse(\Throwable $exception):Response
    {
        $url = Request::url(true);
        Log::error('请求地址：' . $url . PHP_EOL . '文件名：' . $exception->getFile() . PHP_EOL . '行数' . $exception->getLine() . PHP_EOL . '消息：' . $exception->getMessage() . PHP_EOL . 'trace:' . $exception->getTraceAsString() . PHP_EOL . '全：' . json_encode($exception));
        if (request()->isAjax()) {
            // 收集异常数据
            if (env('app_debug')) {
                // 调试模式，获取详细的错误信息
                $data = $this->convertExceptionToArray($exception);
            } else {
                // 部署模式仅显示 Code 和 Message
                $data = $this->convertExceptionToArray($exception);
                if (!config('show_error_msg')) {
                    // 不显示详细错误信息
                    $data['message'] = '你的页面错误了！';
                }
            }
            return $this->returnJson($data['message'], $data['code']);
        } else {
            // 收集异常数据
            if (env('app_debug')) {
                $template = config('app.exception_tmpl');
                ob_start();
                // 调试模式，获取详细的错误信息
                $data = $this->convertExceptionToArray($exception);
            } else {
                $template = config('error.custom_tmpl');
                ob_start();
                // 部署模式仅显示 Code 和 Message
                $data = $this->convertExceptionToArray($exception);
                if (!config('app.show_error_msg')) {
                    // 不显示详细错误信息
                    $data['message'] = '你的页面错误了！';
                }
            }
            extract($data);
            include $template;
            // 获取并清空缓存
            $content = ob_get_clean();
            $response = Response::create($content);
            if ($exception instanceof \think\exception\HttpException) {
                $statusCode = $exception->getStatusCode();
                $response->header($exception->getHeaders());
            }
            if (!isset($statusCode)) {
                $statusCode = 500;
            }
            $response->code($statusCode??500);
            return $response;
        }
    }

    /**
     * @param $msg
     * @param $status
     * @param array $myData
     * @param int $code
     * @param string[] $header
     * @return \think\response\Json
     * Description:json返回
     * Author:sunnier
     * Email:xiaoyao_xiao@126.com
     * 2020-07-04 10:28
     */
    public function returnJson($msg, $status, $myData = array(), $code = 200, $header = array('Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Headers' => 'content-type,token'))
    {
        $data = array(
            'data' => $myData,
            'msg' => $msg,
            'code' => $status,
        );
        return json($data, $code, $header);
    }
}
