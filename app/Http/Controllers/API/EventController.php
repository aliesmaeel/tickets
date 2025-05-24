<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EventApiResource;
use App\Http\Resources\Api\EventSeatsResource;
use App\Models\Event;
use App\Models\EventSeat;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $category_id = $request->input('category_id') ?? null;
        $city_id = $request->input('city_id') ?? null;
        $start_date = $request->input('start_date') ?? null;
        $end_date = $request->input('end_date') ?? null;
        App::setLocale(Auth::user('customer')->lang);
        // make validation on inputs
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:100',
            'category_id' => 'nullable|array',
            'category_id.*' => 'integer|exists:categories,id',
            'city_id' => 'nullable|array',
            'city_id.*' => 'integer|exists:cities,id',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return $this->respondValidationErrors($validator->errors()->toArray());
        }

        try {
            $events = Event::with(['category', 'city'])
                ->whereHas('category', function ($query) use ($category_id) {
                    $query->where('status', 1)
                        ->when($category_id, function ($query) use ($category_id) {
                            if (is_array($category_id)) {
                                $query->whereIn('id', $category_id);
                            } else {
                                $query->where('id', $category_id);
                            }
                        });
                })
                ->whereHas('city', function ($query) use ($city_id) {
                    $query
                        ->when($city_id, function ($query) use ($city_id) {
                            if (is_array($city_id)) {
                                $query->whereIn('id', $city_id);
                            } else {
                                $query->where('id', $city_id);
                            }
                        });
                })
                ->when($start_date, function ($query) use ($start_date) {
                    $query->whereDate('start_time', '>=', $start_date);
                })
                ->when($end_date, function ($query) use ($end_date) {
                    $query->whereDate('end_time', '<=', $end_date);
                })
                ->whereDate('display_start_date', '<=', now())
                ->whereDate('display_end_date', '>=', now())
                ->where('active', 1)
                ->orderBy('id', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            $eventsResource = [
                'data' => EventApiResource::collection($events->items())->resolve(),
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                ],
            ];

            return $this->respondValue($eventsResource, __('messages.events_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->respondError();
        }
    }

    public function show($id)
    {
        try {

            App::setLocale(Auth::user('customer')->lang);

            $event = Event::with(['category', 'city'])
                ->where('id', $id)
                ->where('active', 1)
                ->first();

            if (!$event) {
                return $this->respondNotFound(__('messages.event_not_found'));
            }

            $eventResource = EventApiResource::make($event)->resolve();

            return $this->respondValue($eventResource, __('messages.event_retrieved_successfully'));

        } catch (\Exception $e) {
            return $this->respondError();
        }
    }

    public function getEventSeats($id)
    {
       App::setLocale(Auth::user('customer')->lang);

        try {
            $event = Event::with(['seatClasses', 'seats'])->where('id', $id)->where('active', 1)->firstOrFail();
            $eventResource= new EventSeatsResource($event);

            return $this->respondValue($eventResource, __('messages.event_seats_retrieved_successfully'));

        } catch (\Exception $e) {
            return $this->respondError(__('messages.failed_to_retrieve_event_seats'));
        }
    }

}
