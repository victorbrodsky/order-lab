#! /usr/bin/perl -w

use strict;

use Net::LDAP 0.33;
use Authen::SASL 2.10;

# -------- Adjust to your environment --------
my $adhost      = 'theserver.bla.net';
my $ldap_base   = 'dc=bla,dc=net';
my $ldap_filter = '(&(sAMAccountName=BLAAGROL))';

my $sasl = Authen::SASL->new(mechanism => 'GSSAPI');
my $ldap;

eval {
    $ldap = Net::LDAP->new($adhost,
                           onerror => 'die')
      or  die "Cannot connect to LDAP host '$adhost': '$@'";
    $ldap->bind(sasl => $sasl);
};

if ($@) {
    chomp $@;
    die   "\nBind error         : $@",
          "\nDetailed SASL error: ", $sasl->error,
          "\nTerminated";
}

print "\nLDAP bind() succeeded, working in authenticated state";

my $mesg = $ldap->search(base   => $ldap_base,
                         filter => $ldap_filter);

# -------- evaluate $mesg