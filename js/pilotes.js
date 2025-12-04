fetch("./pilotes.json").then(r => r.json()).then(jsonData =>{
    var carte = L.map('carte-pilotes', {}).setView([46.3630104, 2.9846608], 7);

    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
	attribution: '&copy; Contributrices et contributeurs <a href="http://osm.org/copyright">OpenStreetMap</a>.'
    }).addTo(carte);

    var geojson = L.geoJSON(jsonData, {
	pointToLayer: function(feature, latlng){
	    var marker = L.circleMarker(latlng, {radius: 5});
	    switch(feature.properties.nature_uai_libe.split(' ')[0].toUpperCase()) {
	    case 'ECOLE':
		marker.setStyle({color: 'green'});
		break;
	    case 'COLLEGE':
		marker.setStyle({color: 'blue'});
		break;
	    case 'LYCEE':
		marker.setStyle({color: 'red'});
		break;
	    }
	    return marker;
	    
	}
    }).addTo(carte);
    geojson.bindPopup( l => l.feature.properties.appellation_officielle );

    carte.fitBounds(geojson.getBounds());
})
