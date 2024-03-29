#include <stdlib.h>
#include <sys/types.h>
#include <unistd.h>

int
main (int argc, char *argv[])
{
     setuid (0);

     /* WARNING: Only use an absolute path to the script to execute,
      *          a malicious user might fool the binary and execute
      *          arbitary commands if not.
      * */

     system ("/bin/sh /usr/local/bin/order-lab-tenantmanager/utils/executables/haproxy-restart.sh");

     return 0;
}
   