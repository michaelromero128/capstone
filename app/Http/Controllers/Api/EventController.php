<?php

namespace App\Http\Controllers\Api;

use App\Event;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\EventPhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     */
    public function index(Request $request)
    {
        
        $pagen = $request->paginate ?? 10;
        if(isset($request->zipcode)){
           
            $range = $request->range ?? 25;
           
            $events = Event::zipcode($range, $request->zipcode);
            if(isset($request->q)){
                $events=$events->search($request->q);
            }
            if(isset($request->date)){
                return $events->daterange($request->date)->with('eventPhotos')->simplePaginate($pagen);
            }
            if(isset($request->old) && $request->old == 'yes'){
                return $events->with('eventPhotos')->simplePaginate($pagen); 
            }
            return $events->where('end_date','>=',date('Y-m-d'))->with('eventPhotos')->simplePaginate($pagen);
           
            
        }
        if(isset($request->q)){
            
            $events=Event::search($request->q);
            
            if(isset($request->date)){
                
                return $events->daterange($request->date)->with('eventPhotos')->paginate($pagen);
            }
            if(isset($request->old) && $request->old == 'yes'){
                return $events->with('eventPhotos')->paginate($pagen);
            }
            return $events->where('end_date','>=',date('Y-m-d'))->with('eventPhotos')->paginate($pagen);
        }
        if(isset($request->date)){
            return Event::daterange($request->date)->with('eventPhotos')->paginate($pagen);
        }
        if(isset($request->old) && $request->old == 'yes'){
            return Event::orderBy('start_date')->with('eventPhotos')->paginate($pagen); 
        }
        
        return Event::with('eventPhotos')->where('end_date','>=',date('Y-m-d'))->orderBy('start_date')->paginate($pagen);
        
    }

    //public function searchTerms($request){
    // return Event::search($terms)->get();
    //}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        DB::beginTransaction();
        try{
            $request->validate([
        
                'event_title' => 'required |string |max:10000',
                'event_description' => 'string |required | max:10000',
                'host_organization' => 'required | string |max:10000',
                'event_coordinator_name'  => 'required | string |max:10000',
                'event_coordinator_email' => 'required |email |string |max:10000',
                'event_coordinator_phone' => 'max:100',
                'start_date' => 'string | max:50| date_format:Y-m-d',
                'end_date' => 'string | max:50 | date_format:Y-m-d',
                'start_time' => 'string | max:50',
                'end_time' => 'string | max:50',
                'requirements_major' => 'string |max:255',
                'requirements_year' => 'string | max:255',
                'requirement_one' => 'string |max:10000',
                'requirement_two' => 'string |max:10000',
                'requirement_three' => 'string |max:10000',
                'age_requirement' => 'numeric',
                'minimum_hours' => 'numeric',
                'tags' => 'required | string |max:10000',
                'category' => 'required | string |max:30',
                'shifts' => 'string |max:10000',
                'city' => 'string |max:255',
                'address' => 'required | max:255',
                'zipcode' => 'numeric | required',
                
                ]);
            $params = [
                'event_title' => htmlentities($request->input('event_title')),
                'event_description' => htmlentities($request->input('event_description')),
                'host_organization' => htmlentities($request->input('host_organization')),
                'event_coordinator_name'  => htmlentities($request->input('event_coordinator_name')),
                'event_coordinator_email'  => htmlentities($request->input('event_coordinator_email')),
                'event_coordinator_phone'  => htmlentities($request->input('event_coordinator_phone')),
                'start_date'  => htmlentities($request->input('start_date')),
                'end_date'  => htmlentities($request->input('end_date')),
                'start_time'  => htmlentities($request->input('start_time')),
                'end_time'  => htmlentities($request->input('end_time')),
                'requirements_major'  => htmlentities($request->input('requirements_major')),
                'requirements_year'  => htmlentities($request->input('requirements_year')),
                'requirement_one'  => htmlentities($request->input('requirement_one')),
                'requirement_two'  => htmlentities($request->input('requirement_two')),
                'requirement_three'  => htmlentities($request->input('requirement_three')),
                'age_requirement' => $request->input('age_requirement'),
                'minimum_hours' => $request->input('minimum_hours'),
                'tags'  => htmlentities($request->input('tags')),
                'category'  => htmlentities($request->input('category')),
                'shifts'  => htmlentities($request->input('shifts')),
                'city'  => htmlentities($request->input('city')),
                'address'  => htmlentities($request->input('address')),
                'zipcode' => htmlentities(substr($request->input('zipcode'),0,5)),
            ];
            $event = Event::create($params);
            
            if($request->hasFile('file')){
                request()->validate(['file' =>'required', 'file.*' => 'mimes:jpeg,jpg,png,gif']);
                
                $files = $request->file;
                
                foreach($files as $file){
                        $path = $file->store('docs','public');
                        $event_photo = EventPhoto::create(['filename' => $path]);
                        $event->eventPhotos()->save($event_photo);
                        
                }
            }
        }catch(\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            
            throw new ValidationException($e->errors());
        }
        
        DB::commit();
        $event= Event::where('id',$event->id)->with('eventPhotos')->first();
        Auth::user()->events()->save($event);
        return $event->refresh();
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Event::with('eventPhotos')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $event = Event::findOrFail($id);
        
        $user= Auth::user();
        if($event->user->id != $user->id && ($user->rank != 'elevated' && $user->rank != 'root')){
            //throw  new AuthorizationException('Unauthorized user');
        }
        
        $request->validate([
            
            'event_title' => 'string |max:10000',
            'event_description' => 'string | max:10000',
            'host_organization' => ' string |max:10000',
            'event_coordinator_name'  => 'string |max:10000',
            'event_coordinator_email' => 'email string |max:10000',
            'event_coordinator_phone' => 'max:100',
            'start_date' => 'string | max:50 |format_date:Y-m-d',
            'end_date' => 'string | max:50 | format_date:Y-m-d',
            'start_time' => 'string | max:50',
            'end_time' => 'string | max:50',
            'requirements_major' => 'string |max:255',
            'requirements_year' => 'string | max:255',
            'requirement_one' => 'string |max:10000',
            'requirement_two' => 'string |max:10000',
            'requirement_three' => 'string |max:10000',
            'tags' => 'string |max:10000',
            'category' => 'string |max:30',
            'shifts' => 'string |max:10000',
            'city' => 'string |max:255',
            'address' => 'max:255',
            'zipcode' => 'numeric',
            
        ]);
        $event->update($request->only([
            'event_title',
            'event_description',
            'host_organization',
            'event_coordinator_name',
            'event_coordinator_phone',
            'event_coordinator_email',
            'start_date',
            'end_date',
            'start_time',
            'end_time',
            'requirements_major',
            'requirements_year',
            'requirement_one',
            'requirement_two',
            'requirement_three',
            'tags',
            'category',
            'shifts',
            'city',
            'address',
            'zipcode',
        ]));
        return($event);
    }
    
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $user= Auth::user();
        
        if($event->user->id != $user->id && $user->rank != 'elevated' && $user->rank != 'root'){
            throw  new AuthorizationException('Unauthorized user');
        }
        $event->delete();
        return response()->noContent(200);
    }
}
