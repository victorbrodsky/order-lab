#https://centos.pkgs.org/7/centos-x86_64/perl-LDAP-0.56-6.el7.noarch.rpm.html

use Net::LDAP;

$ldap = Net::LDAP->new( 'a.wcmc-ad.net', port=389, scheme=>'ldap', version=>'LDAPv2', timeout=>5  )  or  die "$@";

#$mesg = $ldap->bind;                         # anonymous bind

#$mesg->code  and  die $mesg->error;          # check for errors

#$srch = $ldap->search( base   => "c=US",     # perform a search
                        #                       filter => "(&(sn=Barr)(o=Texas Instruments))"
                        #                     );

#$srch->code  and  die $srch->error;          # check for errors

#foreach $entry ($srch->entries) { $entry->dump; }

#$mesg = $ldap->unbind;                       # take down session


#$ldap = Net::LDAP->new( 'ldaps://ldap.example.com' )  or  die "$@";

# https://metacpan.org/pod/distribution/perl-ldap/lib/Net/LDAP.pod
# simple bind with DN and password
$mesg = $ldap->bind( 'oli2002',
                     password => 'secret'
                   );

$mesg->code  and  die $mesg->error;          # check for errors

#$result = $ldap->add( 'cn=Barbara Jensen, o=University of Michigan, c=US',
#                      attrs => [
#                        cn          => ['Barbara Jensen', 'Barbs Jensen'],
#                        sn          => 'Jensen',
#                        mail        => 'b.jensen@umich.edu',
#                        objectclass => ['top', 'person',
#                                        'organizationalPerson',
#                                        'inetOrgPerson' ],
#                      ]
#                    );
#
#$result->code  and  warn "failed to add entry: ", $result->error;

$mesg = $ldap->unbind;                       # take down session
