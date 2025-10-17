<?php

namespace App\Http\Controllers\Api\React\Venue;

use Exception;
use App\Models\Venue;
use App\Models\VenueReview;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Venue\VenueReviewRequest;
use App\Http\Resources\Venue\VenueReviewResource;

class VenueReviewController extends Controller
{
    use ApiResponse;

    // Get all reviews for a specific venue
    public function index($venue_id)
    {
        try {
            $venue = Venue::find($venue_id);

            if (!$venue) {
                return $this->error([], 'Venue not found.', 404);
            }

            $reviews = VenueReview::with('user')
                ->where('venue_id', $venue_id)
                ->latest()
                ->get();

            return $this->success(
                VenueReviewResource::collection($reviews),
                'Venue reviews retrieved successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->error([], 'Failed to fetch reviews. ' . $e->getMessage(), 500);
        }
    }

    // Add or Update (Upsert) a review
    public function store(VenueReviewRequest $request, $venue_id)
    {
        DB::beginTransaction();

        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $venue = Venue::find($venue_id);

            if (!$venue) {
                return $this->error([], 'Venue not found.', 404);
            }

            // Create or Update the review
            $review = VenueReview::updateOrCreate(
                ['venue_id' => $venue_id, 'user_id' => $user->id],
                [
                    'comment' => $request->comment,
                    'rating'  => $request->rating,
                ]
            );

            DB::commit();

            return $this->success(
                new VenueReviewResource($review->load('user')),
                'Review submitted successfully.',
                201
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to submit review. ' . $e->getMessage(), 500);
        }
    }

    // Delete review
    public function destroy($id)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                return $this->error([], 'Unauthorized user.', 401);
            }

            $review = VenueReview::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$review) {
                return $this->error([], 'Review not found or unauthorized.', 404);
            }

            $review->delete();

            return $this->success([], 'Review deleted successfully.', 200);
        } catch (Exception $e) {
            return $this->error([], 'Failed to delete review. ' . $e->getMessage(), 500);
        }
    }
}
