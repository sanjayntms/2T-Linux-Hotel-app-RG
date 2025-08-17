# 2T-Linux-Hotel-app-RG
* Deploy this setup in  Azure cloud.
* Test it.
* Create Hyvperv Azure VM with additional data disk. Run ps script available in repo.
* Webvm and dbvm VM deployed using this repo in Azure cloud. Stop VM and export disk to Hyperv Azure VM using azcopy.
* Create new vm with existing disk on hyperv-VM named webvm and dbvm.
* On these vm run
 * cloud-init clean
 * cloud-init init
