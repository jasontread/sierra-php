Name: sierra-php
Version: 1.1
Release: 4
Summary: sierra-php PHP Application Framework
License: Apache License 2.0
Group: Applications/Internet
Source: sierra.tgz
ExclusiveOS: Linux
BuildRoot: %{_tmppath}/%{name}-root
Requires: httpd, php
Packager: jason@technologyinsight.com

%define SIERRA_DIR /var/www/sierra

%description
sierra-php PHP Application Framework
sierra-php is yet another PHP framework. It is compatible with both PHP 4 and 5. 
It provides a foundation of reusable object-oriented code for Linux/Unix PHP 
software implementations. For more information, visit the project website at 
http://code.google.com/p/sierra-php

%prep
%setup -b 0 -n var

%build

%install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT%{SIERRA_DIR}
cp -rf $RPM_BUILD_DIR%{SIERRA_DIR}/* $RPM_BUILD_ROOT%{SIERRA_DIR}/

%files
%defattr(-,root,root)
%{SIERRA_DIR}/*

%clean
rm -rf $RPM_BUILD_ROOT

%post
if [ "$1" = "1" ] ; then
  php %{SIERRA_DIR}/bin/sra-quick-install.php
else
  %{SIERRA_DIR}/bin/sra-clear-cache.php
fi

%postun
if [ $1 = 0 ]; then
  rm -rf %{SIERRA_DIR}
fi

%changelog

