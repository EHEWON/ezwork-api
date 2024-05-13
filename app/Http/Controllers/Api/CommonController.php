<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Province;
use App\Models\City;


class CommonController extends BaseController {

    /**
     * 国家列表
     * @param  Request $request 
     * @return 
     */
    public function country(Request $request){
        $m_country=new Country();
        $result=$m_country->getCountryList($this->lang);
        ok($result);
    }

    /**
     * 省列表
     * @param  Request $request 
     * @return 
     */
    public function province(Request $request, $country_id){
        $m_city=new Province();
        $result=$m_city->getProvinceList($country_id, $this->lang);
        ok($result);
    }

    /**
     * 城市列表
     * @param  Request $request 
     * @return 
     */
    public function city(Request $request, $country_id){
        $m_city=new City();
        $result=$m_city->getCityList($country_id, $this->lang);
        ok($result);
    }
}
