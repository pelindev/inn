<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>NPD Check</title>
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
    </head>
    <body>
        <form action="http://localhost:8083/api/dev/check_status" method="GET">
            <input type="text" id="code" name="code" onkeyup="this.style.backgroundColor = is_valid_inn(this.value) ? '#dfd' : '#fdd'">
            <input type="submit" value="Send" onclick="make_alert_if_not_valid()">
        </form>
        <script type="text/javascript">

            function is_valid_inn(i)
            {
                if ( i.match(/\D/) ) return false;

                var inn = i.match(/(\d)/g);

                if (inn.length == 10)
                {
                    return inn[9] == String(((
                                2*inn[0] + 4*inn[1] + 10*inn[2] +
                                3*inn[3] + 5*inn[4] +  9*inn[5] +
                                4*inn[6] + 6*inn[7] +  8*inn[8]
                            ) % 11) % 10);
                }
                else if (inn.length == 12)
                {
                    return inn[10] == String(((
                                7*inn[0] + 2*inn[1] + 4*inn[2] +
                                10*inn[3] + 3*inn[4] + 5*inn[5] +
                                9*inn[6] + 4*inn[7] + 6*inn[8] +
                                8*inn[9]
                            ) % 11) % 10) && inn[11] == String(((
                                3*inn[0] +  7*inn[1] + 2*inn[2] +
                                4*inn[3] + 10*inn[4] + 3*inn[5] +
                                5*inn[6] +  9*inn[7] + 4*inn[8] +
                                6*inn[9] +  8*inn[10]
                            ) % 11) % 10);
                }

                return false;
            }

            function make_alert_if_not_valid() {
                var inn = document.getElementById('code').value;
                if (!is_valid_inn(inn)) {
                    alert('code is not valid');
                    event.preventDefault();
                }
            }

        </script>

    </body>
</html>
