bunx @tailwindcss/cli -i ./style/input.css -o ./style/style.css --watch


mirror -R --exclude node_modules --exclude .git ./ /htdocs
