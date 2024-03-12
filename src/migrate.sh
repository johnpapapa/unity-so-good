bin/cake cache clear_all
bin/cake migrations rollback -t 0 
bin/cake migrations migrate
bin/cake migrations seed -v
