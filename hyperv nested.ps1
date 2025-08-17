Install-WindowsFeature -Name Hyper-V -IncludeManagementTools -Restart
New-VMSwitch -Name InternalSwitchNAT -SwitchType Internal
New-NetNat –Name LocalNAT –InternalIPInterfaceAddressPrefix “192.168.100.0/24”
New-NetIPAddress -InterfaceAlias "vEthernet (InternalSwitchNAT)" -IPAddress 192.168.100.1 -PrefixLength 24

Netsh interface ip add dnsserver “Ethernet” address=168.63.129.16

Install-WindowsFeature -Name "DHCP" -IncludeManagementTools
Add-DhcpServerv4Scope -Name "Migrate" -StartRange 192.168.100.1 -EndRange 192.168.100.254 -SubnetMask 255.255.255.0 -State Active
Add-DhcpServerv4ExclusionRange -ScopeID 192.168.100.0 -StartRange 192.168.100.1 -EndRange 192.168.100.15
Set-DhcpServerv4OptionValue -DnsDomain $dnsClient.ConnectionSpecificSuffix -DnsServer 168.63.129.16
Set-DhcpServerv4OptionValue -OptionID 3 -Value 192.168.100.1 -ScopeID 192.168.100.0
Set-DhcpServerv4Scope -ScopeId 192.168.100.0 -LeaseDuration 1.00:00:00
Restart-Service dhcpserver