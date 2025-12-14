bunx @tailwindcss/cli -i ./style/input.css -o ./style/style.css --watch


mirror -R --exclude node_modules --exclude .git ./ /htdocs




DB_HOST=localhost
DB_NAME=your_database
DB_USER=root
DB_PASS=your_password


mirror -R --only-newer --exclude-glob-from=exclude-list.txt . /htdocs
