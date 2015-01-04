# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  # box
  config.vm.box = "hashicorp/precise32"
  config.vm.box_url = "https://vagrantcloud.com/hashicorp/precise32"
  # network: forward port 80, 3306
  config.vm.network :forwarded_port, host: 80, guest: 80
  config.vm.network :forwarded_port, host: 3306, guest: 3306
  # bootstrap script
  config.vm.provision :shell, :path => "bootstrap.sh"
end
