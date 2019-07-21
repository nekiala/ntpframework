$(function() {

    Morris.Area({
        element: 'morris-area-chart',
        data: [{
            period: '2013',
            isipa: 10000, esmicom: 2000, isc: 50000
        }, {
            period: '2014',
            isipa: 20000, esmicom: 18000, isc: 70000
        }, {
            period: '2015',
            isipa: 70000, esmicom: 30000, isc: 100000
        }],
        xkey: 'period',
        ykeys: ['isipa', 'esmicom', 'isc'],
        labels: ['ISIPA', 'ESMICOM', 'ISC'],
        pointSize: 2,
        hideHover: 'auto',
        resize: true
    });

    /*Morris.Donut({
        element: 'morris-donut-chart',
        data: [{
            label: "Download Sales",
            value: 12
        }, {
            label: "In-Store Sales",
            value: 30
        }, {
            label: "Mail-Order Sales",
            value: 20
        }],
        resize: true
    });*/

    Morris.Bar({
        element: 'morris-bar-chart',
        data: [{
            y: '2013',
            a: 190,
            b: 200,
            c: 300
        }, {
            y: '2014',
            a: 750,
            b: 615,
            c: 900
        }, {
            y: '2015',
            a: 500,
            b: 430,
            c: 690
        }],
        xkey: 'y',
        ykeys: ['a', 'b', 'c'],
        labels: ['ISIPA', 'ESMICOM', 'ISC'],
        hideHover: 'auto',
        resize: true
    });

});
