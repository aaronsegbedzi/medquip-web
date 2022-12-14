<?php

namespace App\Http\Controllers;

use App\CallEntry;
use App\Equipment;
use App\Hospital;
use App\Http\Requests\HospitalRequest;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HospitalController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		$this->availibility('View Hospitals');
		$index['page'] = 'hospitals';
		$index['hospitals'] = Hospital::select('*')->withCount('equipments')->get();

		return view('hospitals.index', $index);
	}

	public function view($id) {
		$index['page'] = 'my_hospitals';
		$index['hospital'] = Hospital::findOrFail($id);
		$index['counts'] = collect(DB::select("SELECT count(case when c.working_status = 'pending' OR c.working_status IS NULL then 1 end) AS pending, count(case when c.working_status = 'working' then 1 end) AS working, count(case when c.working_status = 'not working' then 1 end) AS not_working, count(*) AS total FROM equipments e LEFT JOIN call_entries c ON e.id = c.equip_id AND c.id = (SELECT MAX(id) FROM call_entries d WHERE d.equip_id = c.equip_id) WHERE e.hospital_id = ?", [$id]))->first();
		$index['equipments'] = DB::select("SELECT e.id, e.name, e.short_name, e.sr_no, x.name AS 'department', IFNULL(c.working_status,'pending') AS 'working_status', c.call_register_date_time, c.call_attend_date_time, c.call_complete_date_time FROM equipments e LEFT JOIN departments x ON x.id = e.department LEFT JOIN call_entries c ON e.id = c.equip_id AND c.id = (SELECT MAX(id) FROM call_entries d WHERE d.equip_id = c.equip_id) WHERE e.hospital_id = ? ORDER BY c.call_complete_date_time DESC", [$id]);
		$index['calls'] = CallEntry::select('*')->join('equipments', 'equipments.id', 'call_entries.equip_id')->where('equipments.hospital_id', $id)->get(); 
		return view('hospitals.view', $index);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		$this->availibility('Create Hospitals');
		$index['page'] = 'hospitals';

		return view('hospitals.create', $index);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(HospitalRequest $request) {
		$hospital = new Hospital;
		$hospital->name = $request->name;
		$hospital->email = $request->email;
		$hospital->phone_no = $request->phone_no;
		$hospital->contact_person = $request->contact_person;
		$hospital->user_id = Auth::user()->id;
		$hospital->mobile_no = $request->mobile_no;
		$hospital->address = $request->address;

		if ($request->hasFile('logo')) {           
            $path = $request->file('logo')->getRealPath();
			$mime = $request->file('logo')->getClientMimeType();
            $doc = file_get_contents($path);
            $base64 = base64_encode($doc);
			$hospital->logo = $base64;
			$hospital->mime = $mime;
		}
		
		$yourString = $hospital->name;
		$result = $this->recursive($request->name);
		$hospital->slug = $result;

		if ($hospital->slug == "") {
			return redirect()->back()->with('flash_message_error', 'please choose another Hospital, there are too many already')->withInput($request->all());
		}

		$hospital->save();
		return redirect('admin/hospitals')->with('flash_message', 'Hospital "' . $hospital->name . '" created');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Hospital  $hospital
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		$this->availibility('Edit Hospitals');
		$index['page'] = 'hospitals';
		$index['hospital'] = Hospital::findOrFail($id);
		return view('hospitals.edit', $index);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hospital  $hospital
	 * @return \Illuminate\Http\Response
	 */
	public function update(HospitalRequest $request, $id) {
		$hospital = Hospital::findOrFail($id);
		$hospital->name = $request->name;
		$hospital->email = $request->email;
		$hospital->contact_person = $request->contact_person;
		$hospital->phone_no = $request->phone_no;
		$hospital->mobile_no = $request->mobile_no;
		$hospital->address = $request->address;

		if ($request->hasFile('logo')) {           
            $path = $request->file('logo')->getRealPath();
			$mime = $request->file('logo')->getClientMimeType();
            $doc = file_get_contents($path);
            $base64 = base64_encode($doc);
			$hospital->logo = $base64;
			$hospital->mime = $mime;
		}

		$hospital->save();

		return redirect('admin/hospitals')->with('flash_message', 'Hospital "' . $hospital->name . '" updated');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Hospital  $hospital
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		$hospital = Hospital::findOrFail($id);
		$hospital->delete();

		return redirect('admin/hospitals')->with('flash_message', 'Hospital "' . $hospital->name . '" deleted');
	}
	public static function availibility($method) {
		$r_p = \Auth::user()->getPermissionsViaRoles()->pluck('name')->toArray();
		if (\Auth::user()->hasPermissionTo($method)) {
			return true;
		} elseif (!in_array($method, $r_p)) {
			abort('401');
		} else {
			return true;
		}
	}
	public static function recursive($yourString) {
		if (strpos($yourString, " ") === false) {
			$vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U",
				" ");
			$yourString = str_replace($vowels, "", $yourString);
			$only_one_word = substr($yourString, 0, 1);
			$only_one_word .= substr($yourString, 1, 1);
			$check = Hospital::where('slug', $only_one_word)->first();
			if ($check == "") {
				$result = $only_one_word;
			}
		} else {
			$words = explode(" ", $yourString);
			$first_char_after_space = substr($words[0], 0, 1);
			$first_char_after_space .= substr($words[1], 0, 1);
			if (array_key_exists(2, $words)) {
				$first_char_after_space .= substr($words[2], 0, 1);
			}
			$check_first_two_char_of_words = Hospital::where('slug', $first_char_after_space)->first();
			if ($check_first_two_char_of_words == "") {
				$result = $first_char_after_space;
			}else{
				$result = "";
			}
			if ($result == "") {
				$vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U",
					" ");
				$yourString = str_replace($vowels, "", $yourString);
				$count = 1;
				for ($i = 1; $i <= strlen($yourString); $i++) {
					$first_char = substr($yourString, 0, 1);
					$first_char .= substr($yourString, $i, 1);
					$check_first_two_char = Hospital::where('slug', $first_char)->first();
					if ($count < strlen($yourString)) {
						if ($check_first_two_char == "") {
							$result = $first_char;
							break;
						}
					}
					$count++;
				}
			}
		}
		return $result;
	}
}
