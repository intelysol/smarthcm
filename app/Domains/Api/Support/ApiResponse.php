<?php
namespace App\Domains\Api\Support;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
final class ApiResponse { public static function success(mixed $data,array $meta=[],array $links=[]):JsonResponse{return response()->json(['success'=>true,'data'=>$data,'meta'=>$meta,'links'=>$links,'errors'=>[],'traceId'=>(string)request()->header('X-Correlation-ID',Str::uuid())]);} public static function error(string $code,string $message,array $details=[],int $status=400):JsonResponse{return response()->json(['success'=>false,'error'=>['code'=>$code,'message'=>$message,'details'=>$details],'traceId'=>(string)request()->header('X-Correlation-ID',Str::uuid())],$status);} }
