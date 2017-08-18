%define module  @SHORT_NAME@
%define vigiconf_confdir %{_sysconfdir}/vigilo/vigiconf/conf.d/

Name:       vigilo-%{module}
Summary:    Vigilo integration plugin for GLPI
Version:    @VERSION@
Release:    @RELEASE@%{?dist}
Source0:    %{name}-%{version}.tar.gz
URL:        http://www.vigilo-nms.com
Group:      Applications/System
BuildRoot:  %{_tmppath}/%{name}-%{version}-%{release}-build
License:    GPLv2
Buildarch:  noarch

Requires:   glpi
Requires:   vigilo-vigiconf

Requires(pre): shadow-utils
Requires(pre): httpd
Requires(pre): vigilo-vigiconf

%description
This package provides a plugin that makes it possible to configure Vigilo
and deploy new configuration files from GLPI's GUI.
This application is part of the Vigilo Project <http://vigilo-nms.com>

%prep
%setup -q

%build

%install
rm -rf $RPM_BUILD_ROOT
make install_pkg \
    DESTDIR=$RPM_BUILD_ROOT \
    SYSCONFDIR=%{_sysconfdir} \
    DATADIR=%{_datadir} \
    INITDIR=%{_initrddir}

%pre
# On autorise l'accès aux fichiers de configuration
# de Vigilo (VigiConf) dans les 2 sens.
usermod -a -G apache vigiconf || :
usermod -a -G vigiconf apache || :
exit 0

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(644,root,root,755)
%doc COPYING.txt
%{_datadir}/%{module}/plugins/
%config(noreplace) %{_sysconfdir}/sudoers.d/%{name}
%defattr(644,vigiconf,apache,770)
%dir %{vigiconf_confdir}/groups/managed/
%dir %{vigiconf_confdir}/hosts/managed/
%dir %{vigiconf_confdir}/hlservices/managed/
%attr(755,root,root) %{_initrddir}/%{name}

%changelog
* Mon May 22 2017 François Poirotte <francois.poirotte@c-s.fr>
- Initial packaging
