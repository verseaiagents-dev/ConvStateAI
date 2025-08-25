(function(x,y){
x=window.verseAIConfig;
fetch(x.apiUrl,{
    method:"POST",
    body:JSON.stringify({
        message:"Hello, how are you?"
    })
})
.then(response=>response.json())
.then(data=>{
    console.log(data);
})
.catch(error=>{
    console.error("Error:",error);
});








})();