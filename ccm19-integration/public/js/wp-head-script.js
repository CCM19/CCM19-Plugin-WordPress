
function insert_head(url) {

    const script=document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;
    script.referrerpolicy='origin';

document.head.appendChild(script);

}

insert_head(HeadParam.insert);
