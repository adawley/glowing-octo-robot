require 'mkmf'

HEADER_DIRS = [
  '/opt/local/include',
  '/usr/local/include',
  '/usr/local/include/ta-lib',
  Config::CONFIG['includedir'],
  '/usr/include'
]
LIB_DIRS = [
  '/opt/local/lib',
  '/usr/local/lib',
  Config::CONFIG['libdir'],
  '/usr/lib',
]

dir_config("talib", HEADER_DIRS, LIB_DIRS)

have_library("ta_lib", "TA_Initialize")
create_makefile("talib")
