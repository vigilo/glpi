%define module  @SHORT_NAME@

Name:       vigilo-%{module}
Summary:    Vigilo integration plugin for GLPI
Version:    @VERSION@
Release:    @RELEASE@%{?dist}
Source0:    %{name}-%{version}.tar.gz
URL:        http://www.projet-vigilo.org
Group:      Applications/System
BuildRoot:  %{_tmppath}/%{name}-%{version}-%{release}-build
License:    GPLv2
Buildarch:  noarch

Requires:   glpi
Requires:   vigilo-vigiconf

%description
This package provides a plugin that makes it possible to configure Vigilo
and deploy new configuration files from GLPI's GUI.
This application is part of the Vigilo Project <http://vigilo-project.org>

%prep
%setup -q

%build

%install
rm -rf $RPM_BUILD_ROOT
make install_pkg \
    DESTDIR=$RPM_BUILD_ROOT \
    SYSCONFDIR=%{_sysconfdir} \
    DATADIR=%{_datadir} \


%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(644,root,root,755)
%doc COPYING.txt
%{_datadir}/%{module}/plugins/
