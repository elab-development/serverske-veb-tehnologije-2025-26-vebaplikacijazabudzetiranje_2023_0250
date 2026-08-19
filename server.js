const app = require("./src/app"); //ucitavanje aplikacije

const PORT = process.env.PORT; //definisanje porta na kojem ce server raditi

app.listen(PORT, () => { //pokretanje servera
    console.log(`Server radi na portu ${PORT}`);
});