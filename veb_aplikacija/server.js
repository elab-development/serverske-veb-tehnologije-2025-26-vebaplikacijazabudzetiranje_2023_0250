const app = require("./src/app"); //ucitavanje aplikacije

const PORT = 3000; //definisanje porta na kojem ce server raditi

app.listen(PORT, () => { //pokretanje servera na definisanom portu
    console.log("Server radi na portu 3000");
});