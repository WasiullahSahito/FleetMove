<?php

namespace Modules\PromotionManagement\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\PromotionManagement\Service\Interfaces\DiscountSetupServiceInterface;
use Modules\PromotionManagement\Transformers\DiscountResource;

class DiscountSetupController extends Controller
{
    protected $discountSetupService;
    public function __construct(DiscountSetupServiceInterface $discountSetupService)
    {
        $this->discountSetupService = $discountSetupService;
    }
 public function list(Request $request): JsonResponse
{
    $user = auth('api')->user();

    if (!$user) {
        return response()->json(responseFormatter(
            constant: DEFAULT_200,
            content: [],
            limit: $request->limit,
            offset: $request->offset
        ));
    }

    $criteria = [
        'user_id'   => $user->id,
        'level_id'  => $user->level?->id ?? null,
        'is_active' => 1,
        'date'      => date('Y-m-d')
    ];

    $discounts = $this->discountSetupService->getUserDiscountList(
        data: $criteria,
        limit: $request->limit,
        offset: $request->offset
    );

    return response()->json(responseFormatter(
        constant: DEFAULT_200,
        content: DiscountResource::collection($discounts),
        limit: $request->limit,
        offset: $request->offset
    ));
}

}
