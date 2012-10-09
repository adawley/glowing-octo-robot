require 'rubygems'
require 'talib_ruby'

# init input data
a = Array.new
10.times { |i| a.push i.to_f }

10.times do |k|
	b = Array.new(10)
	l = TaLib::Function.new("MA")
	# setup input parameter
	l.in_real(0,a);
	# setup optional parameter
	l.opt_int(0,k+2);
	# setup output parameter
	l.out_real(0,b);
	l.call(0,9)
	p "k=#{k+2}"
	p b
	p l.out(0)
end

11.times do |i|
	b = Array.new(20,0)
	l = TaLib::Function.new("CDLDOJI")
	#TaLib.set_candle_settings(TaLib::TA_BodyVeryLong, TaLib::TA_RangeType_RealBody, 1, 1.0)
	#TaLib.set_candle_settings(TaLib::TA_BodyDoji, TaLib::TA_RangeType_HighLow, 10, 0.1)
	TaLib.restore_candle_default_settings(i)

	open =  [4.80]*20 << 0.80
	close = [3.99]*20 << 0.81
	high =  [8.0]*20 << 1.0
	low =   [0.0]*20 << 0.7
	vol   = [1.0]*20 << 1.0
	oi    = [1.0]*20 << 1.0

	l.in_price(0, open, high, low, close, vol, oi)
	l.out_int(0,b)
	l.call(0,20)
	p b
end
