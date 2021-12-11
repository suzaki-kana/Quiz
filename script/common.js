/*
 * 現在時刻からtimeStringで指定された時刻までの秒数を算出
 */
function getTimeDeff(timeString) {
    var times = timeString.split(':');
    var startTime = new Date();
    var endTime = new Date(startTime.getFullYear(), startTime.getMonth(), startTime.getDate()
                        , Number(times[0]), Number(times[1]), 0);
    var elapsedTime = endTime.getTime() - startTime.getTime();
    return Math.floor(elapsedTime / 1000); // 秒の計算
}