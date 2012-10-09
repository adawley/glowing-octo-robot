require 'yahoofinance'

# You can get the historical quote data in 2 formats:
#   1. As an array of raw data.
#   2. As a YahooFinance::HistoricalQuote object.

# Getting the historical quote data as a raw array.
# The elements of the array are:
#   [0] - Date
#   [1] - Open
#   [2] - High
#   [3] - Low
#   [4] - Close
#   [5] - Volume
#   [6] - Adjusted Close


# Getting the data as YahooFinance::HistoricalQuote objects using the
# days API.
YahooFinance::get_HistoricalQuotes_days( 'YHOO', 30 ) do |hq|
  puts "#{hq.symbol},#{hq.date},#{hq.open},#{hq.high},#{hq.low},#{hq.close},#{hq.volume},#{hq.adjClose}"
end
