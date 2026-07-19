const express = require("express"); //ucitava ekspress

const router = express.Router(); //pravi mali ruter


router.get("/", (req, res) => {

    res.json({
        message: "Expense Sharing API radi!"
    });

}); //ispisuje poruku kad neko posalje ovu rutu


module.exports = router; //app ga koristi