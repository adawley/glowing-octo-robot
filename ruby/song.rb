require 'yahoofinance'
require 'talib_ruby'

class Song
	def initialize(name, artist, duration)
		@name = name
		@artist = artist
		@duration = duration
	end

	def to_s
		"Song: #{@name}--#{@artist} (#{@duration})"
	end
end

#class Array
#  def sum
#    inject(0.0) { |result, el| result + el }
#  end

#  def mean 
#    sum / size
#  end
#end

class Trade
	def initialize(symbol, tradePrice, shares, direction)
		@symbol = symbol	
		@tradePrice = tradePrice
		@shares = shares		
		@direction = direction
		@quote = Object.new()
	end	
	attr_accessor :quote, :symbol, :direction	
	def last
		return @quote.lastTrade
	end
	def closes(val=20)
		ret = Hash.new()
		YahooFinance::get_HistoricalQuotes_days( @symbol, val ) do |hq|
  		ret.store(hq.date,hq.adjClose)
		end		
		return ret
	end
end

class LongTrade < Trade
	def initialize(symbol, tradePrice, shares)
		super(symbol, tradePrice, shares, "long")
	end
end

class ShortTrade < Trade
	def initialize(symbol, tradePrice, shares)
		super(symbol, tradePrice, shares, "short")
	end
end

class Pair
	def initialize()
		@trades = Hash.new()
	end
	def addTrade(trade)
		@trades.store(trade.symbol.upcase,trade)
	end
	def symbols()
		return @trades.keys
	end
	def update(quote)
		@trades.fetch(quote.symbol).quote = quote
	end
	def mean()
		a = @trades.fetch("KEY").closes.values		
		b = Array.new()
		l = TaLib::Function.new("SMA")		
		l.in_real(0,a);
		l.opt_int(0,9);
		l.out_real(0,b);
		l.call(0,9);
		return b
	end	
	def to_s		
		total = 0
		stocks = ''
		@trades.each_pair { |key, trade| 
			if trade.instance_of? LongTrade
				total += trade.last
			else
				total -= trade.last
			end	
			stocks += "#{trade.symbol}: #{trade.last}\n"
		}
		return "#{stocks}Pair: #{total}\nMean: #{mean()}"
	end
end

# Set the type of quote we want to retrieve.
# Available type are:
#  - YahooFinanace::StandardQuote
#  - YahooFinanace::ExtendedQuote
#  - YahooFinanace::RealTimeQuote
quote_type = YahooFinance::StandardQuote

# Set the symbols for which we want to retrieve quotes.
# You can include more than one symbol by separating
# them with a ',' (comma).
#quote_symbols = "yhoo,goog"
pair = Pair.new()
pair.addTrade(LongTrade.new("key",565,8.85))
pair.addTrade(ShortTrade.new("rf",659,7.57))

# Get the quotes from Yahoo! Finance.  The get_quotes method call
# returns a Hash containing one quote object of type "quote_type" for
# each symbol in "quote_symbols".  If a block is given, it will be
# called with the quote object (as in the example below).
#while sleep 30
	
	YahooFinance::get_quotes( quote_type, pair.symbols ) do |qt|
		pair.update(qt)
	end

	puts pair
#end

