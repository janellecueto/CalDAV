let months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "Octovber", "Novemeber", "December"];
    let days = ["Sunday", "Monday","Tuesday", "Wednesday","Thursday","Friday","Saturday"];

    //this function takes a date d and returns it in the format: day, month dd, YYYY
    function dateToString(d){
        return days[d.getDay()]+", "+months[d.getMonth()]+" "+d.getDate()+", "+d.getFullYear();
    }
    //takes year month and day indices and converts to YYYY-mm-dd format
    function ymdToDate(y, m, d){
        let ret = "";
        ret += y+"-";
        m++;
        if(m < 10) ret += "0"+m+"-";
        else ret += m;
        if(d < 10) ret += "0"+d;
        else ret += d;
        return ret;
    }
    //takes js Date object and returns string in YYYY-mm-dd format
    function dateToDate(d){
        var y = d.getFullYear();
        var m = d.getMonth()+1;
        m = (m<10 ? "0"+m : m);
        var day = (d.getDate()<10 ? "0"+d.getDate() : d.getDate());
        return y+"-"+m+"-"+day;
    }
    function getMonday(d){
        let day = d.getDay();
        let diff = d.getDate() - day + (day == 0 ? -6:1);
        let ret = new Date(d);
        ret.setDate(diff);
        return ret;
    }
    function timeFormat(t){
        //converts hhmmss to hh:mm AM/PM
        let hrs = t.substring(0,2);
        let ampm = "AM";
        if(parseInt(hrs)>=12){
            ampm = "PM";
            if(parseInt(hrs)>12){
                hrs -= 12;
            }
        }
        return hrs+":"+t.substring(2,4)+" "+ampm;
    }
    function addRow(div, startTime, endTime, emp, desc){
        let $tbl = $("#"+div).find("table").find("tbody");
        // $tbl.empty();
        let newRow = $("<tr>");
        newRow.append("<td>"+timeFormat(startTime)+"</td>");
        newRow.append("<td>"+timeFormat(endTime)+"</td>");
        newRow.append("<td>"+emp+"</td>");
        newRow.append("<td></td>");
        newRow.append("<td>"+desc+"</td>");
        $tbl.append(newRow);
    }

    function pullEvents(start){
        $.ajax({
            method: "GET",
            url: "assets/php/driver.php", 
            data: {
                startDate: start,
            },
            success: function(result){
                //add new row to specified day from result
                let data = JSON.parse(result);
                // console.log(result);
                let day = data["day"];
                console.log(day);
                let events = data["events"]; //this is an array of Event Objects("employee", "summary", "start", "end") 
                // console.log(events);
                events.forEach(function(e){
                    console.log(e);
                    addRow(day, e.start, e.end, e.employee, e.summary);
                });
            },
            error: function(result){

            }
        });
    }