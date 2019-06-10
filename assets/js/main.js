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
    if(!t){
        return "-";
    }
    let hrs = t.substring(0,2);
    let ampm = "AM";
    if(parseInt(hrs)>=12){
        ampm = "PM";
        if(parseInt(hrs)>12){
            hrs -= 12;
            
        }
    }
    return parseInt(hrs)+":"+t.substring(2,4)+" "+ampm;
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

function addMonthDay(date, emp, startTime, endTime, desc){
    let $day = $("td[data-date='"+date+"']");
    let newRow = $("<div>").addClass("minirow");
    let st = timeFormat(startTime);
    let et = timeFormat(endTime);
    newRow.html("<span class='emp'>"+emp+"</span><span class='time'>" + 
    st.substring(0,st.length-3) + "-"+et.substring(0,st.length-3) + 
    "</span><span class='desc'>"+desc+"</span>");
    // console.log(st.substring(0,st.length-3));
    $day.append(newRow);
}

function pullEvents(start, end, month=false, emp=false){
    $.ajax({
        method: "GET",
        url: "assets/php/driver2.php", 
        data: {
            startDate: start,
            endDate: end,
            emp: emp,
        },
        success: function(result){
            console.log(result);
            console.log(emp);
            
            let data = JSON.parse(result);  //result is an array of WeekDay objects
            
            data.forEach(function(e){   
                var day = e["day"];
                var date = e["date"];
                var events = e["events"];    //array of Event objects
                events.forEach(function(e){    
                    // console.log(e);
                    if(month) {
                        addMonthDay(date, e["employee"], e["start"], e["end"], e["summary"]);
                    } else {
                        addRow(day, e["start"], e["end"], e["employee"], e["summary"]);
                    }
                });
            });
            
        },
        error: function(result){
            alert("error: "+result);
        }
    });
}

$("button.view").click(function(){
    $(".selected").toggleClass("selected");
    $(this).toggleClass("selected");
});
