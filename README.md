# 🚀 Azure VM Deployment & Hyper-V Export Setup  

This repository demonstrates how to:  

- 🌐 **Deploy** a two-tier setup (Web VM + DB VM) in **Azure Cloud**  
- ✅ **Test** the deployed setup  
- 💽 **Export** Azure VM disks to **Hyper-V** using `AzCopy`  
- 🖥️ **Create a new Hyper-V VM** with additional data disk  
- ⚡ **Run provided PowerShell scripts** for configuration  

---

## 📦 Deployment Steps  

### 1️⃣ Deploy VMs in Azure  
- The **WebVM** and **DBVM** are deployed using this repo in Azure Cloud.  
- Once deployment is complete, verify that both VMs are running successfully.  

---

### 2️⃣ Export Azure Disks for Hyper-V  
- Stop the **WebVM** and **DBVM** in Azure.  
- Use **AzCopy** to export the VM disks to your Hyper-V environment:  

```powershell
azcopy copy "https://<storageaccount>.blob.core.windows.net/vhds/webvm.vhd?<SAS_TOKEN>" "D:\HyperV\Disks\webvm.vhd"
azcopy copy "https://<storageaccount>.blob.core.windows.net/vhds/dbvm.vhd?<SAS_TOKEN>" "D:\HyperV\Disks\dbvm.vhd"
```

---

### 3️⃣ Create New Hyper-V VMs  
- Create new VMs on Hyper-V using the exported disks:  
  - **VM Name:** `webvm`  
  - **VM Name:** `dbvm`  
- Attach the exported disks as **OS Disks**.  
- Optionally, add an **extra data disk** for testing.  

---

### 4️⃣ Run Cloud-Init on Hyper-V VMs  
Once the VMs boot on Hyper-V, clean and reinitialize `cloud-init`:  

```bash
sudo cloud-init clean
sudo cloud-init init
```

---

### 5️⃣ Run PowerShell Script  
Execute the **PowerShell script** available in this repo to configure your Hyper-V VMs:  

```powershell
.\scripts\setup.ps1
```

---

## ✅ Testing  
- Ensure that the **WebVM** is accessible via HTTP/HTTPS.  
- Verify that the **DBVM** is reachable from WebVM.  
- Confirm the additional data disk is mounted and usable on the Hyper-V VMs.  

---

## 🌈 Summary  

With this setup, you can:  
- Deploy VMs in Azure ☁️  
- Export them to Hyper-V 🖥️  
- Reinitialize with cloud-init 🔄  
- Run automation scripts ⚡  

Perfect for **hybrid cloud demos** and **migration testing**.  

---
