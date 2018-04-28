# Installation 

* git clone https://github.com/sergeynezbritskiy/woo-shipping-with-nova-poshta.git np
* cd np
* svn co https://plugins.svn.wordpress.org/woo-shipping-for-nova-poshta svn.wordpress.org

# Publish new release
 
* gulp build
* glup svn:tag --tag="{your-version-tag}"
* gulp svn:push
* cd svn.wordpress.org
* svn add tags/{your-version-tag}
* svn up
* svn ci -m "Release {your-version-tag} version" --username snezbritskiy
