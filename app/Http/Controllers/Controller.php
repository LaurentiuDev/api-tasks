<?php

namespace App\Http\Controllers;

use App\User;
use GenTux\Jwt\Drivers\FirebaseDriver;
use GenTux\Jwt\GetsJwtToken;
use GenTux\Jwt\JwtToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use GetsJwtToken;

    /** @const string */
    const RESPONSE_SUCCESS = 'success';

    /** @const string */
    const RESPONSE_ERROR = 'error';

    /** @var null */
    protected $data = null;

    /** @var null */
    protected $errorMessage = null;

    /** @var string */
    protected $responseType;

    /** @var int */
    protected $user;

    /** @var FirebaseDriver */
    protected $jwtDriverInterface;

    /**
     * Controller constructor.
     *
     * @param Request $request
     * @param JwtToken $jwt
     * @param FirebaseDriver $jwtDriverInterface
     */
    public function __construct(Request $request, JwtToken $jwt, FirebaseDriver $jwtDriverInterface)
    {
        $this->jwtDriverInterface = $jwtDriverInterface;

        if ($request->has('token')) {
            $decodedToken = $this->decodeToken($request->token);
            if (isset($decodedToken['id'])) {
                $this->user = $decodedToken['id'];
            }
        }
    }

    /**
     * Decode jwt token sent.
     *
     * @param $token
     *
     * @return array
     */
    public function decodeToken($token)
    {
        return $this->jwtDriverInterface->decodeToken($token, env('JWT_SECRET'));
    }

    /**
     * Build the response.
     *
     * @param int $statusCode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function returnResponse($statusCode = Response::HTTP_OK)
    {
        $response = [
            'responseType' => $this->responseType,
            'data' => $this->data,
            'errorMessage' => $this->errorMessage
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * Return not found error.
     *
     * @param null $errorMessage
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnNotFound($errorMessage = null)
    {
        $this->responseType = self::RESPONSE_ERROR;
        $this->errorMessage = $errorMessage ? $errorMessage : 'Entity not found!';

        return $this->returnResponse();
    }

    /**
     * Return bad request error.
     *
     * @param null $errorMessage
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnBadRequest($errorMessage = null)
    {
        $this->responseType = self::RESPONSE_ERROR;
        $this->errorMessage = $errorMessage ? $errorMessage : 'Bad request';

        return $this->returnResponse();
    }

    /**
     * Return unknown error.
     *
     * @param null $errorMessage
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnError($errorMessage = null)
    {
        $this->responseType = self::RESPONSE_ERROR;
        $this->errorMessage = $errorMessage ? $errorMessage : 'Error!!!';

        return $this->returnResponse();
    }

    /**
     * Return success.
     *
     * @param null $data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnSuccess($data = null)
    {
        $this->responseType = self::RESPONSE_SUCCESS;
        $this->data = $data;

        return $this->returnResponse();
    }

    /**
     * Validate jwt token
     *
     * @return bool
     *
     * @throws \GenTux\Jwt\Exceptions\NoTokenException
     */
    protected function validateSession()
    {
        if (!$this->jwtToken()->validate()) {
            return false;
        }

        $token = $this->jwtToken();

        $user = User::where('id', $token->payload('id'))->where('email', $token->payload('context.email'))->first();

        return $user;
    }
}
