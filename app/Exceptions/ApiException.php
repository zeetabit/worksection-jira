<?php
/**
 * Created by PhpStorm.
 * User: zetabit
 * Date: 22.01.18
 * Time: 14:17
 */

namespace App\Exceptions;


use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ApiException extends \Exception
{
  /**
   * Report the exception.
   *
   * @return void
   * @throws \Exception
   */
  public function report()
  {
    $formatter = new LineFormatter(null, null, true);
    $formatter->includeStacktraces(true); // <--

    $handler = new StreamHandler(
      storage_path('logs' . DIRECTORY_SEPARATOR . 'api_exceptions-' . date('Y-m-d') . '.log'), Logger::ERROR
    );
    $handler->setFormatter($formatter);

    $api_exception_log = new Logger('Api Exception Logs');
    $api_exception_log->pushHandler($handler);
    $api_exception_log->addError((string)$this);
  }

  /**
   * Render the exception into an HTTP response.
   *
   * @param  \Illuminate\Http\Request
   * @return \Illuminate\Http\Response
   */
  public function render($request)
  {
    return view('errors.500');
  }
}