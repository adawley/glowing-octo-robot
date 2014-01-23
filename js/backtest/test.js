// requirements
var _ = require('underscore');



// data table
var headers = ["Date","Open","High","Low","Close","Volume","Adj Close"],
    table = [
	["2014-01-21","184.70","184.77","183.05","184.18","88581900","184.18"],
	["2014-01-17","184.10","184.45","183.32","183.64","103899200","183.64"],
	["2014-01-16","184.28","184.66","183.83","184.42","72080000","184.42"],
	["2014-01-15","184.10","184.94","183.71","184.66","96591300","184.66"],
	["2014-01-14","182.29","183.77","181.95","183.67","104588800","183.67"],
	["2014-01-13","183.67","184.18","181.34","181.69","149436000","181.69"],
	["2014-01-10","183.95","184.22","183.01","184.14","101955600","184.14"],
	["2014-01-09","184.11","184.13","182.80","183.64","90529400","183.64"],
	["2014-01-08","183.45","183.83","182.89","183.52","96479300","183.52"],
	["2014-01-07","183.09","183.79","182.95","183.48","86018700","183.48"],
	["2014-01-06","183.49","183.56","182.08","182.36","106828500","182.36"],
	["2014-01-03","183.23","183.60","182.63","182.89","81330600","182.89"],
	["2014-01-02","183.98","184.07","182.48","182.92","119364600","182.92"],
	["2013-12-31","184.07","184.69","183.93","184.69","86119900","184.69"],
	["2013-12-30","183.87","184.02","183.58","183.82","56817500","183.82"]
];



// global var position tells the price functions where we are in the table data
var _position = 0;

var ADJ_CLOSE = headers.indexOf("Adj Close"),
    CLOSE = headers.indexOf("Close"),
    HIGH = headers.indexOf("High"),
    LOW = headers.indexOf("Low");

function _getPrice(arr, index, key){
    if(arr.length-1 >= index){
        return parseFloat(arr[index][key]);
    } else {
        return NaN;
    }
}
function close(arg){
    var loc = arg || 0;
    return _getPrice(table, _position + loc, CLOSE);
}

function high(arg){
    var loc = arg || 0;
    return _getPrice(table, _position + loc, HIGH);
}

function low(arg){
    var loc = arg || 0;
    return _getPrice(table, _position + loc, LOW);
}

function hlc3(){
    return (high() + low() + close()) / 3;
}

function simple_moving_averager(period) {
    var nums = [];
    return function(num) {
        nums.push(num);
        if (nums.length > period){
            nums.splice(0,1);  // remove the first element of the array
        }

        var sum = 0;
        for (var i in nums){
            sum += nums[i];
        }
        var n = period;
        if (nums.length < period){
            n = nums.length;
        }
        return(sum/n);
    };
}




// local maths
var lmath = {
    average: function(arr, len){
        var ret = NaN;

        if(!!len){
            ret = [];
            // simple moving average
            var sma = simple_moving_averager(len);
            for (var i = arr.length - 1; i >= 0; i--) {
                var e = arr[i];
                ret.push(sma(e));
            }
        } else {
            // simple average of all elements
            ret = lmath.sum(arr) / arr.length;
        }

        return ret;
    },
    sum: function(arr){
        if(!!arr){
            return _.reduce(arr, function(memo, num){
                return memo + num;
            },0);
        }
    }
};




/****************************
    MAIN
*/
var sma5 = simple_moving_averager(5);

var plot_data = [],
    i, data, avgLast5Days;

for (i = table.length - 1; i >= 0; i--) {
    // update the position
    _position = i;
    
    shell();
}

function shell(){
    data = (close() - close(1) ) / hlc3();

    if(!isNaN(data)){ return; } // nothing to do otherwise

    plot_data.push(data);
    
    avgLast5Days = sma5(data);
    

}



