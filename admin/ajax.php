function getclass() {
    var sec_id = document.getElementById('section').value;
    $.post("ajax.php", {
        action: 'getclass',
        sec_id: sec_id,
    }, function(data) {
        $('#showclass').html(data);
    });
}

function getsubject() {
    var class_ids = [];
    $('.classList:checked').each(function() {
        class_ids.push($(this).val());
    });
    $.post("ajax.php", {
        action: 'getsubject',
        class_iid: class_ids.join(','),
    }, function(data) {
        $('#showsubject').html(data);
    });
}