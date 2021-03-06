Ubuntu Server VM Config
=======================

Table of Contents
-----------------
  * [Preamble](https://github.com/adawley/glowing-octo-robot/blob/master/ubuntu_vm_server.md#preamble)
  * [Configure Shell](https://github.com/adawley/glowing-octo-robot/blob/master/ubuntu_vm_server.md#configure-shell)
  * [Dev Env](https://github.com/adawley/glowing-octo-robot/blob/master/ubuntu_vm_server.md#dev-env)
  * [Configure X](https://github.com/adawley/glowing-octo-robot/blob/master/ubuntu_vm_server.md#configure-x)
  * [Sublime Text](https://github.com/adawley/glowing-octo-robot/blob/master/ubuntu_vm_server.md#sublime-text)



Preamble
--------

    mkdir ~/opt && mkdir ~/bin
    sudo apt-get install -y xinit
    sudo apt-get install -y build-essential dkms
    sudo mount /dev/cdrom /mnt
    sudo /mnt/VBoxLinuxAdditions.run
    sudo reboot
    
Configure Shell
---------------

[glowing-octo-robot/config/bashrc]

    # fancy prompt
    # 31m = red; 32m = green; 34 = blue; 36m = cyan
    PS1='\[\033[32m\]'             # change color
    PS1="$PS1"'\u@\h '             # user@host<space>
    PS1="$PS1"'\[\033[33m\]'       # change color
    PS1="$PS1"'\w'                 # current working directory
    PS1="$PS1"'\[\033[0m\]'        # change color
    PS1="$PS1"'\n'                 # new line
    PS1="$PS1"'$ '                 # prompt: always $


Dev Env
-------

    sudo apt-get install -y git
    sudo apt-get install -y nodejs npm
    sudo update-alternatives --install /usr/bin/node node /usr/bin/nodejs 10

Configure X
-----------

**chrome**:

    sudo apt-get install chromium-browser

edit `~/bin/web`

    chromium-browser $@ 2>/dev/null &

**xterm**:

    sudo apt-get install xfonts-terminus
    
edit `~/.xinitrc`:

    exec fluxbox &
    exec VBoxClient --clipboard &
    exec xterm

edit `~/.Xdefaults`:
 
    xterm*font: terminus-14
    xterm*boldFont: terminus-14
    xterm*loginShell: true
    xterm*vt100*geometry: 80x50
    xterm*saveLines: 2000
    xterm*charClass: 33:48,35:48,37:48,43:48,45-47:48,64:48,95:48,126:48
    xterm*termName: xterm-color
    xterm*eightBitInput: false
    xterm*foreground: rgb:a8/a8/a8
    xterm*background: rgb:00/00/00
    xterm*color0: rgb:00/00/00
    xterm*color1: rgb:a8/00/00
    xterm*color2: rgb:00/a8/00
    xterm*color3: rgb:a8/54/00
    xterm*color4: rgb:00/00/a8
    xterm*color5: rgb:a8/00/a8
    xterm*color6: rgb:00/a8/a8
    xterm*color7: rgb:a8/a8/a8
    xterm*color8: rgb:54/54/54
    xterm*color9: rgb:fc/54/54
    xterm*color10: rgb:54/fc/54
    xterm*color11: rgb:fc/fc/54
    xterm*color12: rgb:54/54/fc
    xterm*color13: rgb:fc/54/fc
    xterm*color14: rgb:54/fc/fc
    xterm*color15: rgb:fc/fc/fc
    xterm*boldMode: false
    xterm*colorBDMode: true
    xterm*colorBD: rgb:fc/fc/fc

**fluxbox**:
    
    sudo apt-get install fluxbox

set style to zimek_darkblue


sublime text
------------
[http://www.sublimetext.com/3]

    sudo apt-get install libgtk2.0-common
    wget http://c758482.r82.cf2.rackcdn.com/sublime_text_3_build_3065_x64.tar.bz2
    mv sublime_text_3_build_3065_x64.tar.bz2 ~/opt
    tar xvfj sublime_text_3_build_3065_x64.tar.bz2
    rm sublime_text_3_build_3065_x64.tar.bz2

edit `~/bin/e`:

    ~/opt/sublime_text_3/sublime_text $@
    
