$(document).ready(function() {
    $('td.box').each(function () {
        $(this).click(function () {
            var rowColumnsIndexesArray = $(this).attr('id').split('-');
            var rowIndex = rowColumnsIndexesArray[0];
            var columnIndex = rowColumnsIndexesArray[1];
            $.ajax({
                url: Routing.generate('app.main.open_box', {'r': rowIndex, 'c': columnIndex}),
                type: 'GET',
                success: function (data) {
                    var result = $.parseJSON(data);
                    if (result.status_code == 'ENDED') {
                        alert('You lose!');
                    } else {
                        $.each(result.opened_mines, function (rowIndex, columns) {
                            var rowIndex = rowIndex.split('_')[1];
                            $.each(columns, function (columnIndex, value) {
                                var columnIndex = columnIndex.split('_')[1];
                                var box = $('td#'+rowIndex+'-'+columnIndex);
                                $(box).removeClass('box').addClass('open');
                                if (value != 0) {
                                    var mine_class = '';
                                    switch (value) {
                                        case 1:
                                            mine_class = 'one_mine';
                                            break;
                                        case 2:
                                            mine_class = 'two_mines';
                                            break;
                                        case 3:
                                            mine_class = 'three_mines';
                                            break;
                                        case 4:
                                            mine_class = 'four_mines';
                                            break;
                                        case 5:
                                            mine_class = 'five_mines';
                                            break;
                                        case 6:
                                            mine_class = 'six_mines';
                                            break;
                                        case 7:
                                            mine_class = 'seven_mines';
                                            break;
                                        case 8:
                                            mine_class = 'eight_mines';
                                            break;
                                    }
                                    $(box).addClass(mine_class);
                                    $(box).html(value);
                                }
                            });
                        });
                    }
                }
            });
        });
    });
});