<?php
/**
 * NPDController file
 * php version 8.0
 *
 * @category Class
 * @package  MyPackage
 * @author   Pelin Nikita <pelin.dev@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/pelindev/inn.git
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\INN;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

/**
 * NPDController class
 *
 * @category Class
 * @package  MyClass
 * @author   Pelin Nikita <pelin.dev@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/pelindev/inn.git
 */
class NPDController extends Controller
{

    public array $config;

    /**
     * Class constructor
     **/
    public function __construct()
    {
        $this->config = app('config')->get('api');
    }

    /**
     * Get NPD status from database if the entry exists or creates
     * a new one if code is valid
     *
     * @param \Illuminate\Http\Request $request request
     *
     * @return array|\Illuminate\Http\JsonResponse
     **/
    public function getStatus(Request $request) : array|JsonResponse
    {
        try {
            $code = $request->query('code');

            if (!$code) {
                return $this->sendError(
                    'code is required',
                    400
                );
            }
    
            if (!$this->innValidate($code)) {
                return $this->sendError(
                    'code is not valid',
                    400
                );
            }
    
            $inn = INN::where('code', $code)->first();
    
            if ($inn) {
                $updatedAt = new \DateTime($inn->updated_at);
                $updatedAt = $updatedAt->format('Y-m-d');
    
                if ($updatedAt === date('Y-m-d')) {
                    return $this->sendResponse($inn->status, 'ok');
                }
            }
    
            $url = $this->config['status_npd_url'];
    
            $status = Http::post(
                "${url}tracker/taxpayer_status",
                [
                    'inn' => $code,
                    'requestDate' => date('Y-m-d')
                ]
            );

            if (isset($status->json()['code'])) {
                return $this->sendError(
                    $status->json()['message'],
                    400
                );
            }
    
            $inn = $inn ?? new INN();
            $inn->code = $code;
            $inn->status = $status->json();
    
            $inn->save();
    
            return $this->sendResponse($inn->status, 200);
        } catch (\Throwable $e) {
            report($e);

            return $this->sendError(
                'something went wrong',
                500
            );
        }
    }

    /**
     * Checks that code is valid
     *
     * @param int|string $inn inn code
     *
     * @return bool
     **/
    private function innValidate(int|string $inn) : bool
    {
        if (preg_match('/\D/', $inn) || !preg_match('/^[0-9]*$/', $inn)) {
            return false;
        }

        $inn = (string) $inn;
        $len = strlen($inn);

        switch ($len) {
            case 10:
                return $inn[9] === (string)(
                    (
                        (
                            2 * $inn[0] + 4 * $inn[1] + 10 * $inn[2] +
                            3 * $inn[3] + 5 * $inn[4] +  9 * $inn[5] +
                            4 * $inn[6] + 6 * $inn[7] +  8 * $inn[8]
                        ) % 11
                    ) % 10
                );

            case 12:
                $num10 = (string)(
                (
                    (
                        7 * $inn[0] + 2 * $inn[1] + 4 * $inn[2] +
                        8 * $inn[9] + 3 * $inn[4] + 5 * $inn[5] +
                        9 * $inn[6] + 4 * $inn[7] + 6 * $inn[8] +
                        10 * $inn[3]
                    ) % 11
                ) % 10
                );

                $num11 = (string)(
                    (
                    (
                        3 * $inn[0] +  7 * $inn[1] + 2 * $inn[2] +
                        4 * $inn[3] + 10 * $inn[4] + 3 * $inn[5] +
                        5 * $inn[6] +  9 * $inn[7] + 4 * $inn[8] +
                        6 * $inn[9] +  8 * $inn[10]
                    ) % 11
                ) % 10
                );

                return $inn[11] === $num11 && $inn[10] === $num10;

            default:
                return false;
        }
    }
}
