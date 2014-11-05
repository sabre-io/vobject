# Define version and release number
%define version     @PACKAGE_VERSION@
%define release     1
%define php_version 53

Name:          sabre-vobject
Version:       %{version}
Release:       %{release}.php%{php_version}%{?dist}
Summary:       Manipulate iCalendar and vCard objects using PHP
# See https://github.com/fruux/sabre-vobject/blob/master/LICENSE
License:       fruux GmbH
Group:         Development/Libraries
URL:           https://github.com/fruux/sabre-vobject
# Get the source files from https://github.com/fruux/sabre-vobject/tags
Source:        %{name}-%{version}.tar.gz
Buildroot:     %{_tmppath}/%{name}-%{version}-%{release}-root

%description
The VObject library allows you to easily parse and manipulate iCalendar and vCard objects using PHP.
The goal of the VObject library is to create a very complete library, with an easy to use API.

%prep
%setup -q
%build

# Clean the buildroot so that it does not contain any stuff from previous builds
[ "%{buildroot}" != "/" ] && %{__rm} -rf %{buildroot}

# Install the extension
install -d %{buildroot}

# Prepare files
mkdir -p %{buildroot}/usr/share/php/Sabre/Vobject
cp -Ra lib/* %{buildroot}/usr/share/php/Sabre/Vobject
cp -a LICENSE %{buildroot}/usr/share/php/Sabre/Vobject
cp -a README.md %{buildroot}/usr/share/php/Sabre/Vobject
cp -a ChangeLog.md %{buildroot}/usr/share/php/Sabre/Vobject

%clean
[ "%{buildroot}" != "/" ] && %{__rm} -rf %{buildroot}

%files
%defattr(-,root,root,-)
/usr/share/php/Sabre

%doc /usr/share/php/Sabre/Vobject/README.md

%changelog
* Wed Oct 05 2014 Adrian Siminiceanu <adrian.siminiceanu@gmail.com>
 - Initial spec file