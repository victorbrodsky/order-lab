echo ostype="$OSTYPE";
if [[ "$OSTYPE" == "linux-gnu" ]]; then
        # ...
		echo linux;
elif [[ "$OSTYPE" == "darwin" ]]; then
        # Mac OSX
		echo ostype="$OSTYPE";
elif [[ "$OSTYPE" == "cygwin" ]]; then
        # POSIX compatibility layer and Linux environment emulation for Windows
		echo ostype="$OSTYPE";
elif [[ "$OSTYPE" == "msys" ]]; then
        # Lightweight shell and GNU utilities compiled for Windows (part of MinGW)
		echo msys;
		start "https://stackoverflow.com/questions/394230/how-to-detect-the-os-from-a-bash-script";
elif [[ "$OSTYPE" == "win32" ]]; then
        # I'm not sure this can happen.
		echo ostype="$OSTYPE";
elif [[ "$OSTYPE" == "freebsd" ]]; then
        # ...
		echo ostype="$OSTYPE";
else
        # Unknown.
		echo ostype="$OSTYPE";
fi



