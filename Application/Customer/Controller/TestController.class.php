<?php
namespace Customer\Controller;


class TestController extends BaseController {
	
	public function kline() {
		$data =  [
	        ['2013/5/9', 2246.96,2232.97,2221.38,2247.86,12,1200],
	        ['2013/5/10', 2228.82,2246.83,2225.81,2247.67,12,1200],
	        ['2013/5/13', 2247.68,2241.92,2231.36,2250.85,12,1200],
	        ['2013/5/14', 2238.9,2217.01,2205.87,2239.93,12,1200],
	        ['2013/5/15', 2217.09,2224.8,2213.58,2225.19,12,1200],
	        ['2013/5/16', 2221.34,2251.81,2210.77,2252.87,12,1200],
	        ['2013/5/17', 2249.81,2282.87,2248.41,2288.09,12,1200],
	        ['2013/5/20', 2286.33,2299.99,2281.9,2309.39,12,1200],
	        ['2013/5/21', 2297.11,2305.11,2290.12,2305.3,12,1200],
	        ['2013/5/22', 2303.75,2302.4,2292.43,2314.18,12,1200],
	        ['2013/5/23', 2293.81,2275.67,2274.1,2304.95,12,1200],
	        ['2013/5/24', 2281.45,2288.53,2270.25,2292.59,12,1200],
	        ['2013/5/27', 2286.66,2293.08,2283.94,2301.7,12,1200],
	        ['2013/5/28', 2293.4,2321.32,2281.47,2322.1,12,1200],
	        ['2013/5/29', 2323.54,2324.02,2321.17,2334.33,12,1200],
	        ['2013/5/30', 2316.25,2317.75,2310.49,2325.72,12,1200],
	        ['2013/5/31', 2320.74,2300.59,2299.37,2325.53,12,1200],
	        ['2013/6/3', 2300.21,2299.25,2294.11,2313.43,12,1200],
	        ['2013/6/4', 2297.1,2272.42,2264.76,2297.1,12,1200],
	        ['2013/6/5', 2270.71,2270.93,2260.87,2276.86,12,1200],
	        ['2013/6/6', 2264.43,2242.11,2240.07,2266.69,12,1200],
	        ['2013/6/7', 2242.26,2210.9,2205.07,2250.63,12,1200],
	        ['2013/6/13', 2190.1,2148.35,2126.22,2190.1,12,1200]
	    ];
    	$this->assign('history',json_encode($data));
		$this->display();
    }
	
	public function data() {
		$this->ajaxReturn(['20137/2/8', 3000.1,3030.35,2926.22,3050.1,1200,120000]);
	}

	public function history() {

	}
}
