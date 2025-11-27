// Philippine Address Data for Cascading Dropdowns
// Comprehensive data for all 17 regions of the Philippines

interface AddressData {
    [region: string]: {
        [province: string]: {
            [city: string]: string[];
        };
    };
}

const addressData: AddressData = {
    'National Capital Region (NCR)': {
        'Metro Manila': {
            'Manila': ['Ermita', 'Intramuros', 'Malate', 'Paco', 'Pandacan', 'Port Area', 'Quiapo', 'Sampaloc', 'San Andres', 'San Miguel', 'San Nicolas', 'Santa Ana', 'Santa Cruz', 'Santa Mesa', 'Tondo'],
            'Quezon City': ['Bagong Pag-asa', 'Bahay Toro', 'Balingasa', 'Batasan Hills', 'Commonwealth', 'Cubao', 'Fairview', 'Kamuning', 'Libis', 'Novaliches', 'Project 4', 'San Francisco Del Monte', 'Tandang Sora', 'UP Campus'],
            'Makati': ['Bel-Air', 'Dasmariñas', 'Forbes Park', 'Guadalupe Nuevo', 'Guadalupe Viejo', 'Magallanes', 'Olympia', 'Palanan', 'Poblacion', 'Rockwell', 'Salcedo', 'San Antonio', 'San Lorenzo', 'Singkamas', 'Urdaneta', 'Valenzuela'],
            'Pasig': ['Bagong Ilog', 'Bagong Katipunan', 'Caniogan', 'Kapitolyo', 'Manggahan', 'Maybunga', 'Oranbo', 'Palatiw', 'Pinagbuhatan', 'Rosario', 'Sagad', 'San Antonio', 'San Joaquin', 'San Miguel', 'Santa Lucia', 'Santolan', 'Ugong'],
            'Taguig': ['Bagumbayan', 'Bambang', 'Calzada', 'Central Bicutan', 'Central Signal Village', 'Fort Bonifacio', 'Hagonoy', 'Ibayo-Tipas', 'Katuparan', 'Ligid-Tipas', 'Lower Bicutan', 'Maharlika Village', 'Napindan', 'New Lower Bicutan', 'North Daang Hari', 'North Signal Village', 'Palingon', 'Pinagsama', 'San Miguel', 'Santa Ana', 'South Daang Hari', 'South Signal Village', 'Tanyag', 'Tuktukan', 'Upper Bicutan', 'Ususan', 'Wawa', 'Western Bicutan'],
            'Mandaluyong': ['Addition Hills', 'Bagong Silang', 'Barangka Drive', 'Barangka Ibaba', 'Barangka Ilaya', 'Barangka Itaas', 'Buayang Bato', 'Burol', 'Daang Bakal', 'Hagdang Bato Itaas', 'Hagdang Bato Libis', 'Harapin Ang Bukas', 'Highway Hills', 'Hulo', 'Mabini-J. Rizal', 'Malamig', 'Mauway', 'Namayan', 'New Zaniga', 'Old Zaniga', 'Pag-asa', 'Plainview', 'Pleasant Hills', 'Poblacion', 'San Jose', 'Vergara', 'Wack-Wack Greenhills'],
            'Pasay': ['Bagong Ilog', 'Barangay 76', 'F.B. Harrison', 'Libertad', 'Malibay', 'Maricaban', 'Parañaque', 'San Isidro', 'San Rafael', 'San Roque', 'Santa Clara', 'Santo Niño', 'Tramo', 'Villamor'],
            'Parañaque': ['B.F. Homes', 'Baclaran', 'Don Bosco', 'La Huerta', 'Marcelo Green', 'Moonwalk', 'San Antonio', 'San Dionisio', 'San Isidro', 'Santo Niño', 'Sun Valley', 'Tambo', 'Vitalez'],
            'Las Piñas': ['Almanza Uno', 'Almanza Dos', 'B.F. International', 'Daniel Fajardo', 'Elias Aldana', 'Ilaya', 'Manuyo Uno', 'Manuyo Dos', 'Pamplona Uno', 'Pamplona Dos', 'Pamplona Tres', 'Pilar', 'Pulang Lupa Uno', 'Pulang Lupa Dos', 'Talon Uno', 'Talon Dos', 'Talon Tres', 'Talon Kuatro', 'Talon Singko', 'Zapote'],
            'Muntinlupa': ['Alabang', 'Ayala Alabang', 'Buli', 'Cupang', 'Poblacion', 'Putatan', 'Sucat', 'Tunasan'],
            'Caloocan': ['Bagong Barrio', 'Bagong Silang', 'Camarin', 'Grace Park', 'Maypajo', 'Monumento', 'Novaliches', 'Tala', 'Tinajeros'],
            'Malabon': ['Acacia', 'Barrio', 'Concepcion', 'Flores', 'Longos', 'Maysilo', 'Muzon', 'Niugan', 'Panghulo', 'Potrero', 'San Agustin', 'Santolan', 'Tañong', 'Tinajeros', 'Tonsuya', 'Tugatog'],
            'Navotas': ['Bagumbayan North', 'Bagumbayan South', 'Bangculasi', 'Daanghari', 'Navotas East', 'Navotas West', 'North Bay Boulevard North', 'North Bay Boulevard South', 'San Jose', 'San Rafael Village', 'San Roque', 'Sipac-Almacen', 'Tangos North', 'Tangos South', 'Tanza 1', 'Tanza 2'],
            'Valenzuela': ['Arkong Bato', 'Bagbaguin', 'Balangkas', 'Bignay', 'Bisig', 'Canumay East', 'Canumay West', 'Coloong', 'Dalandanan', 'Hen. T. de Leon', 'Isla', 'Karuhatan', 'Lawang Bato', 'Lingunan', 'Mabolo', 'Malanday', 'Malinta', 'Mapulang Lupa', 'Marulas', 'Maysan', 'Palasan', 'Parada', 'Pariancillo Villa', 'Pasolo', 'Poblacion', 'Polo', 'Punturin', 'Rincon', 'Tagalag', 'Ugong', 'Veinte Reales', 'Wawang Pulo'],
            'San Juan': ['Addition Hills', 'Balong-Bato', 'Batis', 'Corazon de Jesus', 'Ermitaño', 'Greenhills', 'Isabelita', 'Kabayanan', 'Little Baguio', 'Maytunas', 'Onse', 'Pasadena', 'Pedro Cruz', 'Progreso', 'Rivera', 'Salapan', 'San Perfecto', 'Sta. Lucia', 'Tibagan', 'West Crame'],
            'Marikina': ['Barangka', 'Concepcion Uno', 'Concepcion Dos', 'Fortune', 'Industrial Valley', 'Jesus Dela Peña', 'Malanday', 'Marikina Heights', 'Nangka', 'Parang', 'San Roque', 'Santa Elena', 'Santo Niño', 'Tañong', 'Tumana'],
        }
    },
    'Cordillera Administrative Region (CAR)': {
        'Abra': {
            'Bangued': ['Agtangao', 'Angad', 'Bangbangar', 'Buneg', 'Calaba', 'Cosili East', 'Cosili West', 'Dangdangla', 'Lingtan', 'Lipcan', 'Macarcarmay', 'Malita', 'Maoay', 'Palao', 'Patucannay', 'Poblacion', 'San Antonio', 'Sao-atan', 'Tablac', 'Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'],
            'Boliney': ['Amti', 'Bolubok', 'Danac East', 'Danac West', 'Dao-angan', 'Dumagas', 'Kilong-olay', 'Poblacion'],
            'Bucay': ['Abang', 'Bangbangcag', 'Bangcagan', 'Banglolao', 'Bugbog', 'Calao', 'Dugong', 'Labon', 'Layugan', 'Nangobongan', 'Pagala', 'Pakiling', 'Poblacion', 'Quimloong', 'Siblong', 'Simbaan'],
        },
        'Apayao': {
            'Kabugao': ['Badduat', 'Dagupan', 'Dibagat', 'Langnao', 'Lenneng', 'Lucab', 'Lussuac', 'Malekkeg', 'Mataddi', 'Poblacion', 'Turod'],
            'Conner': ['Annabuklod', 'Buluan', 'Calafug', 'Katablangan', 'Lappa', 'Manag', 'Nabuangan', 'Poblacion'],
            'Flora': ['Allig', 'Anninipan', 'Atok', 'Bagutong', 'Mallig', 'Poblacion', 'Upper Atok'],
        },
        'Benguet': {
            'Baguio City': ['A. Bonifacio-Caguioa-Rimando', 'Abanao-Zandueta-Kayong-Chugum-Otek', 'Alfonso Tabora', 'Ambiong', 'Andagit', 'Asin Road', 'Atok Trail', 'Aurora Hill Proper', 'Aurora Hill North', 'Aurora Hill South', 'Bagong Lipunan', 'Bakakeng Central', 'Bakakeng North', 'Bal-Marcoville', 'Balsigan', 'Bayan Park East', 'Bayan Park Village', 'Bayan Park West', 'BGH Compound', 'Brookside', 'Camdas', 'Camp 7', 'Camp 8', 'Camp Allen', 'Campo Filipino', 'City Camp Central', 'City Camp Proper', 'Country Club Village', 'Cresencia Village', 'Dagsian Lower', 'Dagsian Upper', 'Dizon Subdivision', 'Dominican-Mirador', 'Dontogan', 'DPS Area', 'Engineers Hill', 'Fairview Village', 'Ferdinand', 'Fort del Pilar', 'Garcia Heights', 'General Luna-Lower', 'General Luna-Upper', 'Gibraltar', 'Greenwater Village', 'Guisad Central', 'Guisad Sorong', 'Happy Hollow', 'Happy Homes', 'Harrison-Claudio Carantes', 'Hillside', 'Holy Ghost Extension', 'Holy Ghost Proper', 'Honeymoon-Holy Ghost', 'Imelda R. Marcos', 'Imelda Village', 'Irisan', 'Kabayanihan', 'Kagitingan', 'Kayang Extension', 'Kayang-Hilltop', 'Kias', 'Legarda-Burnham-Kisad', 'Liwanag-Loakan', 'Loakan Proper', 'Lopez Jaena', 'Lourdes Subdivision Extension', 'Lourdes Subdivision Lower', 'Lourdes Subdivision Proper', 'Lucnab', 'Lualhati', 'Lualhati Baguio', 'Lucban', 'Magsaysay Lower', 'Magsaysay Private Road', 'Magsaysay Upper', 'Malcolm Square-Perfecto', 'Manuel A. Roxas', 'Market Subdivision Upper', 'Middle Quezon Hill Subdivision', 'Military Cut-off', 'Mines View Park', 'Modern Site East', 'Modern Site West', 'MRR-Queen of Peace', 'New Lucban', 'Outlook Drive', 'Pacdal', 'Padre Burgos', 'Padre Zamora', 'Palma-Urbano', 'Phil-Am', 'Pinget', 'Pinsao Pilot Project', 'Pinsao Proper', 'Poliwes', 'Pucsusan', 'Quezon Hill Lower', 'Quezon Hill Middle', 'Quezon Hill Proper', 'Quezon Hill Upper', 'Quirino Hill East', 'Quirino Hill Lower', 'Quirino Hill Middle', 'Quirino Hill West', 'Quirino-Magsaysay Lower', 'Quirino-Magsaysay Upper', 'Rizal Monument Area', 'Rock Quarry Lower', 'Rock Quarry Middle', 'Rock Quarry Upper', 'Salud Mitra', 'San Antonio Village', 'San Luis Village', 'San Roque Village', 'San Vicente', 'Sanitary Camp North', 'Sanitary Camp South', 'Santa Escolastica', 'Santo Rosario', 'Santo Tomas Proper', 'Santo Tomas School Area', 'Scent Hill', 'Scout Barrio', 'Session Road Area', 'Slaughter House Area', 'SLU-SVP Housing Village', 'South Drive', 'Teodora Alonzo', 'Trancoville', 'Victoria Village'],
            'La Trinidad': ['Alapang', 'Alnay', 'Ambiong', 'Bahong', 'Balili', 'Beckel', 'Bineng', 'Betag', 'Cruz', 'Lubas', 'Pico', 'Poblacion', 'Puguis', 'Shilan', 'Tawang', 'Wangal'],
            'Itogon': ['Ampucao', 'Dalupirip', 'Gumatdang', 'Loacan', 'Poblacion', 'Tinongdan', 'Tuding', 'Ucab', 'Virac'],
            'Sablan': ['Bagong', 'Balluay', 'Banangan', 'Banengbeng', 'Bayabas', 'Kamog', 'Pappa', 'Poblacion', 'Taloy'],
            'Tuba': ['Ansagan', 'Camp 1', 'Camp 3', 'Camp 4', 'Nangalisan', 'Poblacion', 'Tabaan Norte', 'Tabaan Sur', 'Taloy Norte', 'Taloy Sur', 'Tadiangan', 'Twin Peaks'],
            'Tublay': ['Ambassador', 'Ambongdolan', 'Ba-ayan', 'Basil', 'Caponga', 'Daclan', 'Tuel', 'Poblacion'],
        },
        'Ifugao': {
            'Lagawe': ['Abinuan', 'Banga', 'Boliwong', 'Burnay', 'Caba', 'Dulao', 'Jucbong', 'Olilicon', 'Poblacion Central', 'Poblacion East', 'Poblacion North', 'Poblacion South', 'Poblacion West', 'Tungngod'],
            'Kiangan': ['Ambabag', 'Baguinge', 'Hucab', 'Julongan', 'Mungayang', 'Nagacadan', 'Poblacion', 'Tuplac'],
        },
        'Kalinga': {
            'Tabuk City': ['Agbannawag', 'Amlao', 'Appas', 'Bagumbayan', 'Balawag', 'Bulo', 'Bulanao Centro', 'Bulanao Norte', 'Calaccad', 'Casigayan', 'Cudal', 'Dagupan Centro', 'Dagupan East', 'Dagupan Weste', 'Dilag', 'Dupag', 'Gobgob', 'Guilayon', 'Ipil', 'Lacnog', 'Lanna', 'Laya East', 'Laya West', 'Lucog', 'Magnao', 'Magsaysay', 'Masablang', 'Nambaran', 'Naneng', 'New Tanglag', 'Poblacion', 'San Juan', 'San Julian', 'Suyang', 'Tuga'],
            'Pinukpuk': ['Aciga', 'Allaguia', 'Ammacian', 'Apatan', 'Ba-ay', 'Ballayangon', 'Bayao', 'Dugpa', 'Limos', 'Mapaco', 'Pakawit', 'Poblacion', 'Taggay', 'Wagud'],
        },
        'Mountain Province': {
            'Bontoc': ['Alab Oriente', 'Alab Proper', 'Balili', 'Bontoc Ili', 'Caluttit', 'Dalican', 'Gonogon', 'Guinaang', 'Maligcong', 'Samoki', 'Talubin', 'Tocucan'],
            'Sagada': ['Aguid', 'Ambasing', 'Ankileng', 'Antadao', 'Balugan', 'Bangaan', 'Besao', 'Dagdag', 'Demang', 'Fidelisan', 'Kilong', 'Madongo', 'Pide', 'Poblacion', 'Suyo', 'Taccong', 'Tetep-an'],
        },
    },
    'Ilocos Region (Region I)': {
        'Ilocos Norte': {
            'Laoag City': ['Araniw', 'Balatong', 'Barit-Pandan', 'Baroid', 'Bengcag', 'Buttong', 'Cabungaan', 'Caaoacan', 'Calayab', 'Camangaan', 'Cataban', 'Cavit', 'Darayday', 'Dibua Norte', 'Dibua Sur', 'Gabu Norte Ext.', 'Gabu Norte', 'Gabu Sur', 'La Paz East', 'La Paz Proper', 'La Paz West', 'Lagui-Sail', 'Lataag', 'Mangato East', 'Mangato West', 'Nanguyudan', 'Pila', 'Rionozo', 'San Agustin', 'San Isidro', 'San Jose', 'San Mateo', 'San Marcos', 'San Matias', 'San Miguel', 'San Pedro', 'San Quirino', 'Santa Angela', 'Santa Balbina', 'Santa Cayetana', 'Santa Joaquina', 'Santa Marcela', 'Santa Rosa', 'Santo Tomas', 'Santo Niño', 'Suyo', 'Tabaa', 'Tangid', 'Talingaan', 'Vira', 'Zamboanga'],
            'Batac City': ['Ablan Sarat', 'Acosta', 'Aglipay', 'Baay', 'Baligat', 'Bequi-Walin', 'Bil-loca', 'Bungon', 'Callaguip', 'Camandingan', 'Camguidan', 'Cangrunaan', 'Capacuan', 'Caunayan', 'Daligdigan', 'Lacub', 'Magnuang', 'Maipalig', 'Mambabanga', 'Nagbacalan', 'Naguirangan', 'Parangopong', 'Payao', 'Poblacion', 'Quiling Norte', 'Quiling Sur', 'Quiom', 'Rayuray', 'San Julian', 'San Mateo', 'San Pedro', 'Santa Catalina', 'Sumader', 'Tabug'],
            'Paoay': ['Bacsil', 'Cabagoan', 'Cabug', 'Cayubog', 'Dolores', 'Laoa', 'Masintoc', 'Nagbacalan', 'Nalasin', 'Nanguyudan', 'Oaig-Daya', 'Pambaran', 'Pannaratan', 'Paratong', 'Pasil', 'Pias Norte', 'Pias Sur', 'Poblacion', 'San Agustin', 'San Blas', 'San Juan', 'San Roque', 'Santa Rita', 'Suba', 'Sungadan', 'Surgui'],
        },
        'Ilocos Sur': {
            'Vigan City': ['I Poblacion', 'II Poblacion', 'III Poblacion', 'IV Poblacion', 'V Poblacion', 'VI Poblacion', 'VII Poblacion', 'VIII Poblacion', 'IX Poblacion', 'Ayudon Norte', 'Ayudon Sur', 'Barangay Beddeng-Daya', 'Barangay Beddeng-Laud', 'Barraca', 'Beddeng Laud', 'Bulala', 'Cabalanggan', 'Cabaroan-Daya', 'Cabaroan-Laud', 'Camangaan', 'Capangpangan', 'Mindoro', 'Nagsangalan', 'Pantay-Daya', 'Pantay-Fatima', 'Pantay-Laud', 'Paoa', 'Paratong', 'Pong-ol', 'Purok-a-bassit', 'Purok-a-dakkel', 'Raois', 'Rugsuanan', 'Salindeg', 'San Jose', 'San Julian Norte', 'San Julian Sur', 'San Pedro', 'San Policarpio', 'Tamag'],
            'Candon City': ['Allangigan 1st', 'Allangigan 2nd', 'Amguid', 'Bagani Campo', 'Bagani Gabor', 'Bagani Tocgo', 'Balingaoan', 'Bagar', 'Calambeg', 'Calao-an', 'Caterman', 'Cubcubbuang', 'Darapidap', 'Langlangca 1st', 'Langlangca 2nd', 'Oaig-Daya', 'Palacapac', 'Paras', 'Paypayad', 'Pao', 'Patpata 1st', 'Patpata 2nd', 'Poblacion', 'Tablac', 'Talogtog', 'Villarica'],
            'Santa': ['Banaoang', 'Bitalag', 'Bucao East', 'Bucao West', 'Cabaritan', 'Cabugao', 'Dapdappig', 'Dili', 'Marcos', 'Mabilbila Norte', 'Mabilbila Sur', 'Nagpanaoan', 'Nangalisan', 'Pagsanahan Norte', 'Pagsanahan Sur', 'Poblacion Norte', 'Poblacion Sur', 'Sacuyya Norte', 'Sacuyya Sur', 'San Miliano', 'San Ramon East', 'San Ramon West', 'Santa Cruz', 'Tabucolan'],
        },
        'La Union': {
            'San Fernando City': ['Abut', 'Apaleng', 'Bacsil', 'Bangbangolan', 'Barangay I', 'Barangay II', 'Barangay III', 'Barangay IV', 'Bato', 'Biday', 'Birunget', 'Cadaclan', 'Camansi', 'Canaoay', 'Carlatan', 'Catbangen', 'Dallangayan Este', 'Dallangayan Oeste', 'Dalumpinas Este', 'Dalumpinas Oeste', 'Ilocanos Norte', 'Ilocanos Sur', 'Langcuas', 'Lingsat', 'Madayegdeg', 'Mameltac', 'Masicong', 'Nagyubuyuban', 'Narra Este', 'Narra Oeste', 'Pagdalagan', 'Pagdaraoan', 'Pao Norte', 'Pao Sur', 'Parian', 'Pias', 'Poro', 'Puspus', 'Sacyud', 'San Agustin', 'San Francisco', 'San Vicente', 'Santiago Norte', 'Santiago Sur', 'Saoay', 'Siboan-Otong', 'Tanqui'],
            'Agoo': ['Ambitacay', 'Balawarte', 'Capas', 'Consolacion', 'Macalva Central', 'Macalva Norte', 'Macalva Sur', 'Nazareno', 'Poblacion', 'San Antonio', 'San Benito Norte', 'San Benito Sur', 'San Joaquin Norte', 'San Joaquin Sur', 'San Jose Norte', 'San Jose Sur', 'San Julian Central', 'San Julian East', 'San Julian Norte', 'San Julian West', 'San Manuel Norte', 'San Manuel Sur', 'San Marcos', 'San Narciso', 'San Nicolas Central', 'San Nicolas Norte', 'San Nicolas Sur', 'San Roque Este', 'San Roque Oeste', 'Santa Ana', 'Santa Barbara', 'Santa Maria', 'Santa Monica', 'Santa Rita Este', 'Santa Rita Norte', 'Santa Rita Oeste', 'Santa Rita Sur'],
            'Bauang': ['Bagbag', 'Bawanta', 'Bayard', 'Binguang', 'Boy-utan', 'Bucayab', 'Cabalitocan', 'Calumbaya', 'Carmay', 'Casilagan', 'Central East', 'Central West', 'Dili', 'Disso-or', 'Guerrero', 'Nagrebcan', 'Parian Este', 'Parian Oeste', 'Paringao', 'Payocpoc Norte', 'Payocpoc Sur', 'Pilar', 'Poblacion', 'Pudoc', 'Quinavite', 'Taberna', 'Urayong'],
        },
        'Pangasinan': {
            'Dagupan City': ['Barangay I', 'Barangay II', 'Barangay III', 'Barangay IV', 'Bacayao Norte', 'Bacayao Sur', 'Barangay', 'Bolosan', 'Bonuan Binloc', 'Bonuan Boquig', 'Bonuan Gueset', 'Calmay', 'Carael', 'Caranglaan', 'Herrero', 'Lasip Chico', 'Lasip Grande', 'Lomboy', 'Lucao', 'Malued', 'Mamalingling', 'Mangin', 'Mayombo', 'Pantal', 'Poblacion Oeste', 'Pogo Chico', 'Pogo Grande', 'Pugaro Suit', 'Salapingao', 'Salisay', 'Tambac', 'Tapuac', 'Tebeng'],
            'Urdaneta City': ['Anonas', 'Bactad East', 'Bayaoas', 'Bolaoen', 'Cabaruan', 'Cabuloan', 'Camanang', 'Camantiles', 'Casantaan', 'Catablan', 'Cayambanan', 'Consolacion', 'Dilan Paurido', 'Labit Proper', 'Labit West', 'Mabanogbog', 'Macalong', 'Nancalobasaan', 'Nancamaliran East', 'Nancamaliran West', 'Nancayasan', 'Oltama', 'Palina East', 'Palina West', 'Pedro T. Orata', 'Pinmaludpod', 'Poblacion', 'San Jose', 'San Vicente', 'Santa Lucia', 'Santo Domingo', 'Sugcong', 'Tipuso', 'Tulong'],
            'San Carlos City': ['Abanon', 'Agdao', 'Anando', 'Ano', 'Antipangol', 'Aponit', 'Bacnar', 'Balaya', 'Balayong', 'Baldog', 'Balococ', 'Bani', 'Bega', 'Bocboc', 'Bogaoan', 'Bolosan', 'Bonifacio', 'Buenglat', 'Burgos-Padlan', 'Cacaritan', 'Caingal', 'Calobaoan', 'Calomboyan', 'Caarosipan', 'Cobol', 'Cruz', 'Doyong', 'Gamata', 'Guelew', 'Ilang', 'Inerangan', 'Isabang', 'Libas', 'Lilimasan', 'Longos', 'Lucban', 'Mabalbalino', 'Mabini', 'Magtaking', 'Malacañang', 'Maliwara', 'Mamarlao', 'Manzon', 'Matagdem', 'M.Soriano', 'Naguilayan', 'Nilentap', 'Padilla-Gomez', 'Pagal', 'Palaming', 'Palaris', 'Palospos', 'Pangalangan', 'Pangoloan', 'Pangpang', 'Paitan-Panoypoy', 'Parayao', 'Payapa', 'Payar', 'Perez Boulevard', 'Polo', 'Quezon Boulevard', 'Quintong', 'Rizal Avenue', 'Salinap', 'Sampaloc', 'San Juan', 'San Pedro-Taloy', 'Sapinit', 'Supo', 'Talang', 'Tamayo', 'Tandang Sora', 'Tarece', 'Tarectec', 'Tayambani', 'Tebag', 'Turac'],
            'Alaminos City': ['Alos', 'Amandiego', 'Amangbangan', 'Balangobong', 'Baleyadaan', 'Bisocol', 'Bolaney', 'Bued', 'Cabatuan', 'Cayucay', 'Dulacac', 'Inerangan', 'Landoc', 'Linmansangan', 'Lucap', 'Maawi', 'Macatiw', 'Magsaysay', 'Mona', 'Palamis', 'Pandan', 'Pangapisan', 'Poblacion', 'Pocalpocal', 'Pogo', 'Quibuar', 'Sabangan', 'San Antonio', 'San Jose', 'San Roque', 'San Vicente', 'Santa Maria', 'Tanaytay', 'Tangcarang', 'Tawintawin', 'Telbang', 'Victoria'],
            'Lingayen': ['Aliwekwek', 'Baay', 'Balangobong', 'Balococ', 'Bantayan', 'Basing', 'Capandanan', 'Domalandan', 'Dorongan', 'Dulag', 'Estanza', 'Lasip', 'Libsong', 'Malawa', 'Malimpuec', 'Maniboc', 'Matalava', 'Naguelguel', 'Namolan', 'Pangapisan', 'Poblacion', 'Quibaol', 'Rosario', 'Sabangan', 'Talogtog', 'Tonton', 'Tumbar', 'Wawa'],
            'Bayambang': ['Alinggan', 'Amancosiling Norte', 'Amancosiling Sur', 'Amangonan-Balibago', 'Ambuetel', 'Bani', 'Banaban', 'Batangcawa', 'Beleng', 'Bical Norte', 'Bical Sur', 'Buayaen', 'Cadre-Site', 'Carungay', 'Duera', 'Dusoc', 'Hermoza', 'Imelda', 'Langiran', 'Ligue', 'M.H. del Pilar', 'Macabito', 'Magsaysay', 'Maigpa', 'Maningding', 'Mangayao', 'Nalsian Norte', 'Nalsian Sur', 'Olo', 'Pantol', 'Poblacion Norte', 'Poblacion Sur', 'Pugo', 'Reynado', 'San Gabriel 1st', 'San Gabriel 2nd', 'Santa Cruz', 'Sanlibo', 'Tanolong', 'Topdac', 'Troneng', 'Warding'],
            'Manaoag': ['Baguinay', 'Babasit', 'Cabanbanan', 'Calomboyan', 'Ican', 'Licsi', 'Lipit Norte', 'Lipit Sur', 'Pantal', 'Pao', 'Poblacion', 'Pugaro', 'San Ramon', 'Sapang', 'Tebuel'],
            'Malasiqui': ['Abanon', 'Agdao', 'Amacalan', 'Andangin', 'Apaya', 'Bacayao Norte', 'Bacayao Sur', 'Balite', 'Binalay', 'Bobon', 'Bongar', 'Bulo', 'Butao', 'Cabatling', 'Dinalaoan', 'Gatang', 'Goliman', 'Gomez', 'Guilig', 'Ican', 'IngalaoTr', 'Lasip', 'Libsong', 'Mabulitec', 'Malimpec', 'Muelat', 'Nalsian', 'Paitan Norte', 'Paitan Sur', 'Palapar', 'Pasima', 'Poblacion', 'Potiocan', 'San Julian', 'Tobor', 'Tomling', 'Viado'],
            'Binalonan': ['Balangobong', 'Bued', 'Bugayong', 'Canarvacanan', 'Capas', 'Cili', 'Dumayat', 'Linoc', 'Mangcasuy', 'Moreno', 'Pasileng Norte', 'Pasileng Sur', 'Poblacion', 'San Felipe Central', 'San Felipe Sur', 'San Pablo', 'Santa Catalina', 'Santa Maria Norte', 'Santo Tomas', 'Sumabnit', 'Vacante'],
            'Rosales': ['Acop', 'Bakit-Bakit', 'Bakitbakit', 'Cabalaoangan Norte', 'Cabalaoangan Sur', 'Camangaan', 'Capitan Tomas', 'Carmay', 'Carmen East', 'Carmen West', 'Casanicolasan', 'Coliling', 'Don Antonio Village', 'Guiling', 'Palakipak', 'Pangaoan', 'Poblacion East', 'Poblacion West', 'Rabago', 'Rizal', 'Salvacion', 'San Antonio', 'San Bartolome', 'San Isidro', 'San Luis', 'San Pedro East', 'San Pedro West', 'San Vicente', 'Station District', 'Tomana East', 'Tomana West', 'Zone I', 'Zone II', 'Zone III', 'Zone IV', 'Zone V'],
            'Pozorrubio': ['Alipangpang', 'Amagbagan', 'Balacag', 'Banding', 'Batakil', 'Bobonan', 'Buneg', 'Cablong', 'Casanfernandoan', 'Dilan', 'Don Benito', 'Haway', 'Imbalbalatong', 'Laoac', 'Maambal', 'Malabago', 'Malasin', 'Nama', 'Nantangalan', 'Palacpalac', 'Palguyod', 'Poblacion I', 'Poblacion II', 'Poblacion III', 'Poblacion IV', 'Rosario', 'Sugcong', 'Talogtog', 'Tulnac', 'Villegas'],
            'Sison': ['Agat', 'Alibeng', 'Amagbagan', 'Artacho', 'Asan Norte', 'Asan Sur', 'Bantay', 'Bila', 'Binmeckeg', 'Bulaney East', 'Bulaney West', 'Calunetan', 'Camangaan', 'Cauringan', 'Cabaruan', 'Dungon', 'Esperanza', 'Inmalog', 'Killo', 'Labayug', 'Paldit', 'Pinmilapil', 'Poblacion Central', 'Poblacion Norte', 'Poblacion Sur', 'Pogo', 'Sagunto', 'Tara-tara'],
            'Santa Barbara': ['Alibago', 'Balingueo', 'Banaoang', 'Banzal', 'Botao', 'Cablong', 'Carusocan', 'Dalongue', 'Erfe', 'Gueguesangen', 'Leet', 'Malanay', 'Maningding', 'Maronong', 'Mabitbit', 'Nilombot', 'Palaris', 'Palospos', 'Patayac', 'Poblacion', 'Primicias', 'Sapang', 'Sonquil', 'Tebag', 'Tuliao', 'Ventenilla'],
        },
    },
    'Cagayan Valley (Region II)': {
        'Batanes': {
            'Basco': ['Airport', 'Chanarian', 'Ihubok I', 'Ihubok II', 'Kaychanarianan', 'Kayhuvokan', 'Kayvaluganan', 'San Antonio', 'San Joaquin'],
            'Itbayat': ['Raele', 'San Rafael', 'Santa Lucia', 'Santa Maria', 'Santa Rosa'],
        },
        'Cagayan': {
            'Tuguegarao City': ['Annafunan East', 'Annafunan West', 'Atulayan Norte', 'Atulayan Sur', 'Bagay', 'Buntun', 'Caggay', 'Capatan', 'Carig Norte', 'Carig Sur', 'Caritan Centro', 'Caritan Norte', 'Caritan Sur', 'Cataggaman Nuevo', 'Cataggaman Pardo', 'Cataggaman Viejo', 'Centro 1', 'Centro 2', 'Centro 3', 'Centro 4', 'Centro 5', 'Centro 6', 'Centro 7', 'Centro 8', 'Centro 9', 'Centro 10', 'Centro 11', 'Centro 12', 'Dadda', 'Gosi Norte', 'Gosi Sur', 'Larion Alto', 'Larion Bajo', 'Leonarda', 'Libag Norte', 'Libag Sur', 'Linao East', 'Linao Norte', 'Linao West', 'Nambbalan Norte', 'Nambbalan Sur', 'Pallua Norte', 'Pallua Sur', 'Pengue', 'Reyes', 'San Gabriel', 'Tagga', 'Tanza', 'Ugac Norte', 'Ugac Sur'],
            'Aparri': ['Backiling', 'Bangag', 'Binalan', 'Bisagu', 'Bulala Norte', 'Bulala Sur', 'Caagaman', 'Centro 1', 'Centro 2', 'Centro 3', 'Centro 4', 'Centro 5', 'Centro 6', 'Centro 7', 'Centro 8', 'Centro 9', 'Centro 10', 'Centro 11', 'Centro 12', 'Centro 13', 'Centro 14', 'Dodan', 'Fuga Island', 'Gaddang', 'Linao', 'Mabanguc', 'Macanaya', 'Maura', 'Minanga', 'Navagan', 'Paruddun Norte', 'Plaza', 'Punta', 'San Antonio', 'Sanja', 'Tallungan', 'Toran', 'Zinarag'],
            'Sanchez Mira': ['Abagao', 'Callao', 'Centro', 'Dalauey', 'Duruaron', 'Mabangac', 'Maluyo', 'Marede', 'Masisit', 'Poblacion', 'San Isidro', 'Santa Barbara', 'Santa Cruz', 'Sidem', 'Swamp Area', 'Tappa', 'Topao'],
        },
        'Isabela': {
            'Ilagan City': ['Alibagu', 'Allinguigan 1st', 'Allinguigan 2nd', 'Allinguigan 3rd', 'Arusip', 'Bagong Bayan', 'Bagong Silang', 'Banisor', 'Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8', 'Barungcaao', 'Bato', 'Bigao', 'Cabannungan 1st', 'Cabannungan 2nd', 'Cagumitan', 'Camarao', 'Centro Poblacion', 'Circuncision', 'Dalibubon', 'Danipa', 'Del Pilar', 'Dibuluan', 'Fugu', 'Fuyo', 'Gayong', 'Lullutan', 'Maconacon', 'Malalam', 'Manaring', 'Morado', 'Nanaguan', 'Namnama', 'Osmeña', 'Pallanan', 'Pasungol', 'Quimalabasa', 'Rang-ayan', 'Rogus', 'San Andres', 'San Antonio', 'San Felipe', 'San Rodrigo', 'San Vicente', 'Santa Barbara', 'Santa Isabel', 'Santa Maria', 'Santa Victoria', 'Santo Tomas', 'Silag', 'Sinippil', 'Sipay', 'Soyung', 'Tangcul', 'Tumaru'],
            'Santiago City': ['Abra', 'Ambalatungan', 'Balintocatoc', 'Baluarte', 'Bannawag', 'Batal', 'Bella Cruz', 'Buenavista', 'Cabulay', 'Calao East', 'Calao West', 'Calaocan', 'Centro East', 'Centro West', 'Divisoria', 'Dubinan East', 'Dubinan West', 'Luna', 'Mabini', 'Malvar', 'Nabbuan', 'Naggasican', 'Patul', 'Plaridel', 'Rizal', 'Rosario', 'Sagana', 'Salvador', 'San Andres', 'San Isidro', 'San Jose', 'Sinili', 'Victory Norte', 'Victory Sur', 'Villa Domingo'],
            'Cauayan City': ['Alicaocao', 'Alinam', 'Amobocan', 'Andarayan', 'Baringin Norte', 'Baringin Sur', 'Buena Suerte', 'Bugallon', 'Buyon', 'Cabugao', 'Carabatan Bacareno', 'Carabatan Chica', 'Carabatan Grande', 'Carabatan Punta', 'Cassap Fuera', 'Catalina', 'Cauayan Distric', 'Dabburab', 'De Vera', 'Disimuray', 'District I', 'District II', 'District III', 'Duminit', 'Faustino', 'Gagabutan', 'Gappal', 'Guayabal', 'Labinab', 'Lingaling', 'Maligaya', 'Manaoag', 'Marabulig I', 'Marabulig II', 'Minante I', 'Minante II', 'Naganacan', 'Nagrumbuan', 'Nungnungan I', 'Nungnungan II', 'Pinoma', 'Rizal', 'Rogus', 'San Antonio', 'San Fermin', 'San Francisco', 'San Isidro', 'San Luis', 'San Pablo', 'Santa Luciana', 'Santa Maria', 'Sillawit', 'Sinippil', 'Tagaran', 'Turayong', 'Union', 'Villa Concepcion', 'Villa Luna'],
        },
        'Nueva Vizcaya': {
            'Bayombong': ['Bansing', 'Bonfal East', 'Bonfal West', 'Busilac', 'Casat', 'Ipil-Cuneg', 'La Torre Norte', 'La Torre Sur', 'Luyang', 'Magapuy', 'Magsaysay', 'Masoc', 'Paitan', 'Salvacion', 'San Nicolas Norte', 'San Nicolas Sur', 'Santa Rosa', 'Sawmill', 'Tulaog', 'Vista Alegre'],
            'Solano': ['Aggub', 'Bangaan', 'Bangar', 'Bascaran', 'Concepcion', 'Curifang', 'Dadap', 'Lactawan', 'Osmeña', 'Pilar', 'Poblacion North', 'Poblacion South', 'Quezon', 'Quirino', 'Roxas', 'San Juan', 'Tucal', 'Uddiawan', 'Wacal'],
        },
        'Quirino': {
            'Cabarroguis': ['Andres Bonifacio', 'Aurora', 'Buneg', 'Canabuan', 'Concepcion', 'Del Pilar', 'Gundaway', 'Lucban', 'Magsaysay', 'Mantaroc', 'Rizal', 'San Marcos', 'San Pedro', 'Santa Rosa', 'Villa Ventura', 'Villamor'],
            'Diffun': ['Andres Bonifacio', 'Bannawag', 'Buenavista', 'Callang', 'Guinsayan', 'Latangan', 'Magsaysay', 'Maligaya', 'Mapalad', 'Marshall', 'Nipaco', 'Poblacion I', 'Poblacion II', 'San Fernando', 'San Pascual', 'Tungol'],
        },
    },
    'Central Luzon (Region III)': {
        'Pampanga': {
            'Angeles City': ['Agapito del Rosario', 'Amsic', 'Anunas', 'Balibago', 'Claro M. Recto', 'Cuayan', 'Cutcut', 'Cutud', 'Lourdes North West', 'Lourdes Sur', 'Malabanias', 'Margot', 'Mining', 'Pampang', 'Pandan', 'Pulungbulu', 'Pulung Cacutud', 'Pulung Maragul', 'Salapungan', 'San Jose', 'San Nicolas', 'Santo Cristo', 'Santo Rosario', 'Sapalibutad', 'Sapangbato', 'Tabun', 'Virgen de los Remedios'],
            'San Fernando': ['Alasas', 'Baliti', 'Bulaon', 'Calulut', 'Dela Paz Norte', 'Dela Paz Sur', 'Del Carmen', 'Del Pilar', 'Del Rosario', 'Dolores', 'Juliana', 'Lara', 'Lourdes', 'Magliman', 'Maimpis', 'Malino', 'Malpitic', 'Pandaras', 'Panipuan', 'Pulung Bulu', 'Quebiawan', 'Saguin', 'San Agustin', 'San Felipe', 'San Isidro', 'San Jose', 'San Juan', 'San Nicolas', 'San Pedro', 'Santa Lucia', 'Santa Teresita', 'Santo Niño', 'Santo Rosario', 'Sindalan', 'Telabastagan'],
            'Mabalacat': ['Atlu-Bola', 'Bundagul', 'Cacutud', 'Calumpang', 'Camachiles', 'Dapdap', 'Dau', 'Dolores', 'Duquit', 'Lakandula', 'Mabiga', 'Macapagal Village', 'Mamatitang', 'Mangalit', 'Manuali', 'Marcos Village', 'Paralaya', 'Poblacion', 'San Francisco', 'San Joaquin', 'Sapang Biabas', 'Tabun'],
            'Guagua': ['Ascomo', 'Bancal', 'Lambac', 'Maquiapo', 'Natividad', 'Poblacion', 'Pulungmasle', 'San Agustin', 'San Juan Nepomuceno', 'San Nicolas I', 'San Nicolas II', 'San Pablo', 'San Roque', 'Santa Filomena', 'Santa Ines', 'Santo Cristo', 'Santo Niño', 'Sivio'],
            'Porac': ['Babo Pangulo', 'Balubad', 'Calzadang Bayu', 'Camias', 'Diaz', 'Hacienda Dolores', 'Jalung', 'Mancatian', 'Manibaug Libutad', 'Manibaug Paralaya', 'Manibaug Pasig', 'Manuali', 'Mitla Proper', 'Palat', 'Pias', 'Planas', 'Poblacion', 'Pulung Santol', 'Sapang Uwak', 'Sepung Bulaun', 'Sula', 'Villar'],
            'Mexico': ['Acli', 'Anao', 'Balas', 'Bical', 'Camuning', 'Cawayan', 'Concepcion', 'Divisoria', 'Eden', 'Lagundi', 'Laput', 'Laug', 'Masamat', 'Masangsang', 'Nueva Victoria', 'Pandacaqui', 'Pangatian', 'Panipuan', 'Poblacion', 'Sabanilla', 'San Antonio', 'San Carlos', 'San Jose Malino', 'San Juan', 'San Miguel', 'San Nicolas', 'San Pablo', 'San Patricio', 'San Vicente', 'Santa Cruz', 'Santa Maria', 'Santo Cristo', 'Santo Rosario', 'Suclaban', 'Tangle'],
        },
        'Bulacan': {
            'Malolos': ['Anilao', 'Atlag', 'Babatnin', 'Bagna', 'Bagong Bayan', 'Balayong', 'Balite', 'Bangkal', 'Barihan', 'Bulihan', 'Bungahan', 'Caingin', 'Calero', 'Caliligawan', 'Canalate', 'Caniogan', 'Catmon', 'Cofradia', 'Dakila', 'Guinhawa', 'Ligas', 'Liyang', 'Longos', 'Look 1st', 'Look 2nd', 'Lugam', 'Mabolo', 'Mambog', 'Masile', 'Matimbo', 'Mojon', 'Namayan', 'Niugan', 'Pamarawan', 'Panasahan', 'Pinagbakahan', 'San Agustin', 'San Gabriel', 'San Juan', 'San Pablo', 'San Vicente', 'Santiago', 'Santisima Trinidad', 'Santo Cristo', 'Santo Niño', 'Santo Rosario', 'Sumapang Bata', 'Sumapang Matanda', 'Taal', 'Tikay'],
            'Meycauayan City': ['Bagbaguin', 'Bahay Pare', 'Bancal', 'Banga', 'Bayugo', 'Camalig', 'Calvario', 'Caingin', 'Hulo', 'Iba', 'Langka', 'Lawa', 'Libtong', 'Liputan', 'Longos', 'Malhacan', 'Pajo', 'Pandayan', 'Pantoc', 'Perez', 'Poblacion', 'Saluysoy', 'St. Francis', 'Tugatog', 'Ubihan', 'Zamora'],
            'San Jose del Monte': ['Assumption', 'Bagong Buhay I', 'Bagong Buhay II', 'Bagong Buhay III', 'Citrus', 'Ciudad Real', 'Dulong Bayan', 'Fatima I', 'Fatima II', 'Fatima III', 'Fatima IV', 'Fatima V', 'Francisco Homes-Guijo', 'Francisco Homes-Mulawin', 'Francisco Homes-Narra', 'Francisco Homes-Yakal', 'Gaya-Gaya', 'Graceville', 'Gumaoc Central', 'Gumaoc East', 'Gumaoc West', 'Kaybanban', 'Kaypian', 'Lawang Pari', 'Minuyan I', 'Minuyan II', 'Minuyan III', 'Minuyan IV', 'Minuyan V', 'Minuyan Proper', 'Muzon', 'Paradise III', 'Poblacion', 'San Isidro', 'San Manuel', 'San Martin I', 'San Martin II', 'San Martin III', 'San Martin IV', 'San Pedro', 'San Rafael I', 'San Rafael III', 'San Rafael IV', 'San Rafael V', 'San Roque', 'Santa Cruz I', 'Santa Cruz II', 'Santa Cruz III', 'Santa Cruz IV', 'Santa Cruz V', 'Santo Cristo', 'Santo Niño', 'Sapang Palay', 'St. Martin de Porres', 'Tungkong Mangga'],
            'Marilao': ['Abangan Norte', 'Abangan Sur', 'Ibayo', 'Lambakin', 'Lias', 'Loma de Gato', 'Nagbalon', 'Patubig', 'Poblacion I', 'Poblacion II', 'Prenza I', 'Prenza II', 'Santa Rosa I', 'Santa Rosa II', 'Saog', 'Tabing Ilog'],
            'Bocaue': ['Antipona', 'Bagumbayan', 'Bambang', 'Batia', 'Biñang 1st', 'Biñang 2nd', 'Bolacan', 'Bundukan', 'Bunlo', 'Caingin', 'Duhat', 'Igulot', 'Lolomboy', 'Poblacion', 'Sulucan', 'Taal', 'Tambubong', 'Turo', 'Wakas'],
            'Balagtas': ['Borol 1st', 'Borol 2nd', 'Dalig', 'Longos', 'Panginay', 'Pulong Gubat', 'San Juan', 'Santol', 'Wawa'],
            'Santa Maria': ['Bagbaguin', 'Balasing', 'Buenavista', 'Camangyanan', 'Catmon', 'Cay Pombo', 'Caysio', 'Guyong', 'Lalakhan', 'Mag-asawang Sapa', 'Mahabang Parang', 'Manggahan', 'Parada', 'Poblacion', 'Pulong Bunga', 'San Gabriel', 'San Jose Patag', 'San Vicente', 'Santa Clara', 'Santa Cruz', 'Silangan', 'Tabing Bakod'],
        },
        'Nueva Ecija': {
            'Cabanatuan City': ['Aduas Norte', 'Aduas Sur', 'Bagong Buhay', 'Bagong Sikat', 'Bakero', 'Bakod Bayan', 'Balite', 'Bangad', 'Barlis', 'Barrera District', 'Bernardo District', 'Bitas', 'Bonifacio District', 'Buliran', 'Camp Tinio', 'Caalibangbangan', 'Calawagan', 'Dicarma', 'General Luna', 'Hermogenes', 'Isla', 'Kalikid Norte', 'Kalikid Sur', 'Kapitan Pepe', 'Lagare', 'M.S. Garcia', 'Magsaysay Norte', 'Magsaysay Sur', 'Maligaya', 'Mancol', 'Mayapyap Norte', 'Mayapyap Sur', 'Melliza', 'Meña', 'Obrero', 'Pagas', 'Palagay', 'Pamaldan', 'Patalac', 'Piñahan', 'Polilio', 'Policarpo', 'Poblacion', 'Rizdelis', 'Sampaloc', 'San Juan Accfa', 'San Roque District', 'Sangitan', 'Santa Arcadia', 'Santor', 'Sapang', 'Sinipit', 'Sumacab Este', 'Sumacab Norte', 'Sumacab Sur', 'Tallungan', 'Valdefuente', 'Valle Cruz', 'West Triangle', 'Zulueta'],
            'Palayan City': ['Abanador', 'Atate', 'Balangkare Norte', 'Balangkare Sur', 'Bambanaba', 'Caballero', 'Canaan East', 'Canaan West', 'Doña Josefa', 'Langka', 'Maligaya', 'Mapait', 'Nauzon', 'Palayan City Hall', 'Poblacion', 'San Roque', 'Santa Maria'],
            'Gapan': ['Bayanihan', 'Bungo', 'Bulak', 'Kapanikian', 'Mahipon', 'Mabunga', 'Malimba', 'Marelo', 'Pambuan', 'Parcutela', 'Parang Mangga', 'Patalan', 'Poblacion', 'Pulong Buli', 'San Isidro', 'San Lorenzo', 'San Nicolas', 'San Roque', 'San Vicente', 'Santo Cristo Norte', 'Santo Cristo Sur', 'Santo Niño', 'Santo Rosario'],
            'Muñoz': ['Balante', 'Bantug', 'Burgos', 'Caniogan', 'Catalanacan', 'Mapangpang', 'Poblacion East', 'Poblacion North', 'Poblacion South', 'Poblacion West', 'Rang-ayan', 'Rizal', 'San Antonio', 'San Felipe', 'San Jose', 'San Juan', 'San Pablo', 'San Pedro', 'Santo Domingo', 'Villa Nati'],
            'Talavera': ['Bagong Sikat', 'Bagong Silang', 'Bangad', 'Bantug', 'Casulucan', 'Columbian', 'Gulod', 'Janopol', 'Mabuhay', 'Maligaya', 'Marcos District', 'Poblacion', 'San Antonio', 'San Isidro', 'San Pascual', 'San Ricardo', 'Santa Maria'],
            'San Jose City': ['Abar 1st', 'Abar 2nd', 'Bagong Buhay', 'Caanawan', 'Calaocan', 'Camanacsacan', 'Culaylay', 'Kaingin', 'Kaliwanagan', 'Malasin', 'Manicla', 'Parang Mangga', 'Poblacion', 'San Juan', 'San Vicente', 'Santo Tomas', 'Sibut', 'Tabulac'],
        },
        'Tarlac': {
            'Tarlac City': ['Aguso', 'Alvindia', 'Amucao', 'Armenia', 'Asturias', 'Atioc', 'Balanti', 'Balete', 'Balibago I', 'Balibago II', 'Balingcanaway', 'Banaba', 'Bantog', 'Bora', 'Buenavista', 'Buhilit', 'Burot', 'Calingcuan', 'Capehan', 'Carangian', 'Care', 'Central', 'Culubasa', 'Cut-cut I', 'Cut-cut II', 'Dalayap', 'Dela Paz', 'Dolores', 'Laoang', 'Ligtasan', 'Lourdes', 'Mabini', 'Maligaya', 'Maliwalo', 'Mapalacsiao', 'Mapalad', 'Matatalaib', 'Paraiso', 'Poblacion', 'Salapungan', 'San Carlos', 'San Francisco', 'San Isidro', 'San Jose', 'San Jose de Urquico', 'San Juan Bautista', 'San Luis', 'San Manuel', 'San Miguel', 'San Nicolas', 'San Pablo', 'San Pascual', 'San Rafael', 'San Roque', 'San Sebastian', 'San Vicente', 'Santa Cruz', 'Santa Maria', 'Santo Cristo', 'Santo Domingo', 'Santo Niño', 'Sapang Maragul', 'Sapang Tagalog', 'Sepung Calzada', 'Sinait', 'Suizo', 'Tariji', 'Tibag', 'Tibagan', 'Trinidad', 'Ungot', 'Villa Bacolor'],
            'Capas': ['Aranguren', 'Balete', 'Bueno', 'Cristo Rey', 'Cubcub', 'Cutcut I', 'Cutcut II', 'Dolores', 'Lawy', 'Manga', 'Maruglu', 'O`Donnell', 'Poblacion I', 'Poblacion II', 'San Nicolas', 'Santa Juliana', 'Santa Lucia', 'Santo Rosario', 'Talaga'],
            'Concepcion': ['Alejo', 'Bacolor', 'Balete', 'Capehan', 'Culatingan', 'Dungan', 'Magao', 'Mabilog', 'Maguinao', 'Maligaya', 'Minane', 'Parulung', 'Pitabunan', 'Poblacion I', 'Poblacion II', 'San Bartolome', 'San Francisco', 'San Jose', 'San Juan', 'San Nicolas I', 'San Nicolas II', 'San Roque', 'Santa Monica', 'Talimundoc'],
            'Paniqui': ['Acocolao', 'Apulid', 'Balaoang', 'Balite', 'Bantog', 'Bredicia', 'Burgos', 'Cacamilingan Norte', 'Cacamilingan Sur', 'Caluyucan', 'Coral', 'Dapdap', 'Estacion', 'Mabini', 'Mabilang', 'Maratudo', 'Nagmisaan', 'Nancamarinan', 'Poblacion Norte', 'Poblacion Sur', 'Rang-ayan', 'Salomague Norte', 'Salomague Sur', 'San Carlos', 'San Juan', 'San Nicolas', 'Santa Cruz', 'Santo Domingo'],
            'Gerona': ['Abagon', 'Acidig', 'Amacalan', 'Balingcanaway Norte', 'Balingcanaway Sur', 'Bawa', 'Bularit', 'Burot', 'Calayaan', 'Calius', 'Carbonel', 'Cayaoan', 'Danzo', 'Dicolor', 'Doliman', 'Magaspac', 'Malayep', 'Matapitap Norte', 'Matapitap Sur', 'Matigui', 'Padapada', 'Pinasling', 'Poblacion East', 'Poblacion West', 'Quezon', 'Rizal', 'Salapungan', 'San Agustin', 'San Antonio', 'San Bartolome', 'San Jose', 'San Juan', 'San Vicente', 'Santiago', 'Santo Domingo', 'Sulipa', 'Tagumbao', 'Tangcaran', 'Tugui Grande', 'Villa Marina'],
        },
        'Zambales': {
            'Olongapo City': ['Asinan', 'Banicain', 'Barretto', 'East Bajac-Bajac', 'East Tapinac', 'Gordon Heights', 'Kalaklan', 'Mabayuan', 'New Cabalan', 'New Ilalim', 'New Kababae', 'New Kalalake', 'Old Cabalan', 'Pag-asa', 'Santa Rita', 'West Bajac-Bajac', 'West Tapinac'],
            'Iba': ['Amungan', 'Bangantalinga', 'Dirita-Baloguen', 'Lipay-Dingin-Panibuatan', 'Palali-Palanginan', 'Poblacion', 'San Agustin', 'Santa Barbara', 'Santo Rosario', 'Zone 1', 'Zone 2'],
            'Subic': ['Aningway Sacobia', 'Asinan Proper', 'Asinan Poblacion', 'Baraca-Camachile', 'Calapacuan', 'Cawag', 'Ilwas', 'Mangan-Vaca', 'Matain', 'Naugsol', 'Pamatawan', 'San Isidro', 'Santo Tomas', 'Wawandue'],
        },
        'Aurora': {
            'Baler': ['Barangay I', 'Barangay II', 'Barangay III', 'Barangay IV', 'Barangay V', 'Buhangin', 'Calabuanan', 'Obligacion', 'Pingit', 'Reserva', 'Sabang', 'Suklayin', 'Zabali'],
            'Casiguran': ['Bianuan', 'Calabgan', 'Calangcuasan', 'Calantas', 'Cozo', 'Culat', 'Dibacong', 'Dibet', 'Dilaguidi', 'Dinapigue', 'Ditale', 'Esperanza', 'Esteves', 'Galintuja', 'Marikit', 'Mariones', 'Poblacion', 'San Ildefonso', 'Tabas', 'Tinib'],
        },
        'Bataan': {
            'Balanga City': ['Bagong Silang', 'Bagumbayan', 'Cabog-Cabog', 'Cataning', 'Central', 'Cupang North', 'Cupang Proper', 'Cupang West', 'Dangcol', 'Ibayo', 'Lote', 'Malabia', 'Munting Batangas', 'Poblacion', 'Puerto Rivas Ibaba', 'Puerto Rivas Itaas', 'San Jose', 'Sibacan', 'Talisay', 'Tenejero', 'Tortugas', 'Tuyo'],
            'Mariveles': ['Alas-asin', 'Alion', 'Baseco Country', 'Batangas I', 'Batangas II', 'Cabcaben', 'Camaya', 'Ipag', 'Lucanin', 'Maligaya', 'Mt. View', 'Poblacion', 'San Carlos', 'San Isidro', 'Sisiman', 'Townsite'],
        },
    },
    'Calabarzon (Region IV-A)': {
        'Cavite': {
            'Bacoor': ['Alima', 'Aniban I', 'Banalo', 'Bayanan', 'Buhay Na Tubig', 'Campo Santo', 'Daang Bukid', 'Digman', 'Dulong Bayan', 'Habay I', 'Habay II', 'Kaingin', 'Ligas I', 'Ligas II', 'Ligas III', 'Mabolo I', 'Mabolo II', 'Mabolo III', 'Maliksi I', 'Maliksi II', 'Maliksi III', 'Mambog I', 'Mambog II', 'Mambog III', 'Mambog IV', 'Mambog V', 'Marinig', 'Molino I', 'Molino II', 'Molino III', 'Molino IV', 'Molino V', 'Niog I', 'Niog II', 'Niog III', 'Panapaan I', 'Panapaan II', 'Panapaan III', 'Panapaan IV', 'Panapaan V', 'Panapaan VI', 'Queens Row Central', 'Queens Row East', 'Queens Row West', 'Real I', 'Real II', 'Salinas I', 'Salinas II', 'Salinas III', 'Salinas IV', 'San Nicolas I', 'San Nicolas II', 'San Nicolas III', 'Sineguelasan', 'Tabing Dagat', 'Talaba I', 'Talaba II', 'Talaba III', 'Talaba IV', 'Talaba V', 'Talaba VI', 'Talaba VII', 'Zapote I', 'Zapote II', 'Zapote III', 'Zapote IV', 'Zapote V'],
            'Imus': ['Alapan I-A', 'Alapan I-B', 'Alapan II-A', 'Alapan II-B', 'Anabu I-A', 'Anabu I-B', 'Anabu I-C', 'Anabu I-D', 'Anabu I-E', 'Anabu I-F', 'Anabu I-G', 'Anabu II-A', 'Anabu II-B', 'Anabu II-C', 'Anabu II-D', 'Anabu II-E', 'Anabu II-F', 'Bayan Luma I', 'Bayan Luma II', 'Bayan Luma III', 'Bayan Luma IV', 'Bayan Luma V', 'Bayan Luma VI', 'Bayan Luma VII', 'Bayan Luma VIII', 'Bucandala I', 'Bucandala II', 'Bucandala III', 'Bucandala IV', 'Bucandala V', 'Buhay na Tubig', 'Carsadang Bago I', 'Carsadang Bago II', 'Magdalo', 'Malagasang I-A', 'Malagasang I-B', 'Malagasang I-C', 'Malagasang I-D', 'Malagasang I-E', 'Malagasang I-F', 'Malagasang I-G', 'Malagasang II-A', 'Malagasang II-B', 'Malagasang II-C', 'Malagasang II-D', 'Malagasang II-E', 'Malagasang II-F', 'Poblacion I-A', 'Poblacion I-B', 'Poblacion I-C', 'Poblacion II-A', 'Poblacion II-B', 'Poblacion III-A', 'Poblacion III-B', 'Poblacion IV-A', 'Poblacion IV-B', 'Poblacion IV-C', 'Poblacion IV-D', 'Tanzang Luma I', 'Tanzang Luma II', 'Tanzang Luma III', 'Tanzang Luma IV', 'Tanzang Luma V', 'Tanzang Luma VI'],
            'Dasmariñas': ['Bagong Bayan', 'Burol', 'Emmanuel Bergado I', 'Emmanuel Bergado II', 'Langkaan I', 'Langkaan II', 'Luzviminda', 'Paliparan I', 'Paliparan II', 'Paliparan III', 'Sabang', 'Salawag', 'Salitran I', 'Salitran II', 'Salitran III', 'Salitran IV', 'San Agustin I', 'San Agustin II', 'San Agustin III', 'San Andres I', 'San Andres II', 'San Antonio de Padua I', 'San Antonio de Padua II', 'San Dionisio', 'San Esteban', 'San Francisco I', 'San Francisco II', 'San Isidro Labrador I', 'San Isidro Labrador II', 'San Jose', 'San Lorenzo Ruiz I', 'San Lorenzo Ruiz II', 'San Luis I', 'San Luis II', 'San Manuel I', 'San Manuel II', 'San Mateo', 'San Miguel I', 'San Miguel II', 'San Nicolas I', 'San Nicolas II', 'San Roque', 'San Simon', 'Santa Cristina I', 'Santa Cristina II', 'Santa Cruz I', 'Santa Cruz II', 'Santa Fe', 'Santa Lucia', 'Victoria Reyes', 'Zone I', 'Zone II', 'Zone III', 'Zone IV'],
            'Tagaytay': ['Asisan', 'Bagong Tubig', 'Calabuso', 'Dapdap East', 'Dapdap West', 'Francisco', 'Guinhawa North', 'Guinhawa South', 'Iruhin Central', 'Iruhin East', 'Iruhin South', 'Iruhin West', 'Kaybagal Central', 'Kaybagal North', 'Kaybagal South', 'Mag-Asawang Ilat', 'Maharlika East', 'Maharlika West', 'Maitim 2nd Central', 'Maitim 2nd East', 'Maitim 2nd West', 'Mendez Crossing East', 'Mendez Crossing West', 'Neogan', 'Patutong Malaki North', 'Patutong Malaki South', 'Sambong', 'San Jose', 'Silang Crossing North', 'Silang Crossing South', 'Sungay East', 'Sungay West', 'Tolentino East', 'Tolentino West', 'Zambal'],
        },
        'Laguna': {
            'Calamba': ['Bagong Kalsada', 'Banadero', 'Banlic', 'Barandal', 'Batino', 'Bubuyan', 'Bucal', 'Bunggo', 'Burol', 'Camaligan', 'Canlubang', 'Halang', 'Hornalan', 'Kay-Anlog', 'La Mesa', 'Laguerta', 'Lawa', 'Lecheria', 'Lingga', 'Looc', 'Mabato', 'Makiling', 'Mapagong', 'Masili', 'Maunong', 'Mayapa', 'Milagrosa', 'Paciano Rizal', 'Palingon', 'Palo-Alto', 'Pansol', 'Parian', 'Poblacion', 'Punta', 'Puting Lupa', 'Puypuy', 'Real', 'Saimsim', 'Sampiruhan', 'San Cristobal', 'San Jose', 'San Juan', 'Sirang Lupa', 'Sucol', 'Turbina', 'Ulango', 'Uwisan'],
            'Biñan': ['Biñan', 'Bungahan', 'Canlalay', 'Casile', 'De La Paz', 'Ganado', 'Langkiwa', 'Loma', 'Malaban', 'Malamig', 'Mampalasan', 'Platero', 'Poblacion', 'San Antonio', 'San Francisco', 'San Jose', 'San Vicente', 'Santo Domingo', 'Santo Niño', 'Santo Tomas', 'Soro-soro', 'Timbao', 'Tubigan', 'Zapote'],
            'Santa Rosa': ['Aplaya', 'Balibago', 'Caingin', 'Dila', 'Don Jose', 'Ibaba', 'Kanluran', 'Labas', 'Macabling', 'Malitlit', 'Malusak', 'Market Area', 'Pooc', 'Pulong Santa Cruz', 'Santo Domingo', 'Sinalhan', 'Tagapo', 'Poblacion'],
            'San Pedro': ['Bagong Silang', 'Calendola', 'Chrysanthemum', 'Cuyab', 'Estrella', 'G.S.I.S.', 'Landayan', 'Langgam', 'Laram', 'Magsaysay', 'Maharlika', 'Narra', 'Nueva', 'Pacita I', 'Pacita II', 'Poblacion', 'Riverside', 'Rosario', 'Sampaguita Village', 'San Antonio', 'San Lorenzo Ruiz', 'San Roque', 'San Vicente', 'Santo Niño', 'United Bayanihan', 'United Better Living'],
        },
        'Rizal': {
            'Antipolo': ['Bagong Nayon', 'Beverly Hills', 'Cupang', 'Dalig', 'dela Paz', 'Inarawan', 'Mambugan', 'Mayamot', 'San Jose', 'San Luis', 'San Roque', 'Santa Cruz', 'Santo Niño', 'Calawis'],
            'Cainta': ['San Andres', 'San Isidro', 'San Juan', 'San Roque', 'Santo Domingo', 'Santo Niño'],
            'Taytay': ['Dolores', 'San Isidro', 'San Juan', 'Santa Ana'],
        },
        'Batangas': {
            'Batangas City': ['Alangilan', 'Balagtas', 'Balete', 'Banaba Center', 'Bolbok', 'Cuta', 'Kumintang Ibaba', 'Kumintang Ilaya', 'Libjo', 'Maapas', 'Mabacong', 'Malitam', 'Pallocan West', 'Poblacion', 'San Agapito', 'San Agustin', 'San Jose Sico', 'San Miguel', 'San Pedro', 'Santa Clara', 'Santa Rita Aplaya', 'Simlong', 'Sorosoro Ibaba', 'Sorosoro Ilaya', 'Tabangao Aplaya', 'Tabangao Dao', 'Talahib Pandayan', 'Talahib Payapa', 'Tinga Itaas', 'Tinga Labac', 'Wawa'],
            'Lipa': ['Adya', 'Anilao', 'Antipolo del Norte', 'Antipolo del Sur', 'Bagong Pook', 'Balintawak', 'Banaybanay', 'Bolbok', 'Bugtong na Pulo', 'Bulacnin', 'Bulaklakan', 'Calamias', 'Cumba', 'Dagatan', 'Duhatan', 'Halang', 'Inosluban', 'Kayumanggi', 'Latag', 'Lodlod', 'Lumbang', 'Mabini', 'Malagonlong', 'Malitlit', 'Marauoy', 'Mataas na Lupa', 'Munting Pulo', 'Pagolingin Bata', 'Pagolingin East', 'Pagolingin West', 'Pangao', 'Pinagkawitan', 'Pinagtongulan', 'Plaridel', 'Poblacion Barangay 1', 'Poblacion Barangay 2', 'Poblacion Barangay 3', 'Poblacion Barangay 4', 'Poblacion Barangay 5', 'Poblacion Barangay 6', 'Poblacion Barangay 7', 'Poblacion Barangay 8', 'Poblacion Barangay 9', 'Poblacion Barangay 9-A', 'Poblacion Barangay 10', 'Poblacion Barangay 11', 'Poblacion Barangay 12', 'Pusil', 'Quezon', 'Rizal', 'Sabang', 'Sampaguita', 'San Benito', 'San Carlos', 'San Celestino', 'San Francisco', 'San Guillermo', 'San Jose', 'San Lucas', 'San Salvador', 'San Sebastian', 'Santa Catalina', 'Santa Cruz', 'Santo Niño', 'Santo Toribio', 'Sapac', 'Sico', 'Talisay', 'Tambo', 'Tangob', 'Tanguay', 'Tibig', 'Tipacan'],
            'Tanauan': ['Altura Bata', 'Altura Matanda', 'Altura South', 'Ambulong', 'Bagbag', 'Bagumbayan', 'Balele', 'Banjo East', 'Banjo Laurel', 'Banjo West', 'Bilog-bilog', 'Boot', 'Cale', 'Darasa', 'Gonzales', 'Hidalgo', 'Janopol', 'Janopol Oriental', 'Laurel', 'Luyos', 'Mabini', 'Malaking Pulo', 'Maria Paz', 'Maugat', 'Montana', 'Natatas', 'Pagaspas', 'Pantay Matanda', 'Pantay na Bata', 'Poblacion Barangay 1', 'Poblacion Barangay 2', 'Poblacion Barangay 3', 'Poblacion Barangay 4', 'Poblacion Barangay 5', 'Poblacion Barangay 6', 'Poblacion Barangay 7', 'Sala', 'Sambat', 'San Jose', 'Santol', 'Santor', 'Sulpoc', 'Suplang', 'Talaga', 'Tinurik', 'Trapiche', 'Ulango', 'Wawa'],
        },
        'Quezon': {
            'Lucena': ['Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8', 'Barangay 9', 'Barangay 10', 'Cotta', 'Dalahican', 'Domoit', 'Gulang-Gulang', 'Ibabang Dupay', 'Ibabang Iyam', 'Ibabang Talim', 'Ilayang Dupay', 'Ilayang Iyam', 'Ilayang Talim', 'Isabang', 'Kulapi', 'Majes', 'Market View', 'Mayao Crossing', 'Mayao Kanluran', 'Mayao Parada', 'Mayao Silangan', 'Ransohan', 'Salinas', 'Talipan'],
            'Tayabas': ['Alitao', 'Alsam Ibaba', 'Alsam Ilaya', 'Ayaas', 'Baguio', 'Banilad', 'Bukal', 'Calantas', 'Calimpak', 'Camaysa', 'Dapdap', 'Domoit Kanluran', 'Domoit Silangan', 'Gibanga', 'Ibas', 'Ilasan Ibaba', 'Ilasan Ilaya', 'Ipilan', 'Isabang', 'Katigan Kanluran', 'Katigan Silangan', 'Lakawan', 'Lalo', 'Lawigue', 'Lita', 'Malaoa', 'Masin', 'Mate', 'Mateuna', 'Nangka Ibaba', 'Nangka Ilaya', 'Opias', 'Palale Ibaba', 'Palale Ilaya', 'Palale Kanluran', 'Palale Silangan', 'Pandakaki', 'Pook', 'Potol', 'San Diego Zone I', 'San Diego Zone II', 'San Diego Zone III', 'San Diego Zone IV', 'San Isidro Zone I', 'San Isidro Zone II', 'San Isidro Zone III', 'San Isidro Zone IV', 'San Roque Zone I', 'San Roque Zone II', 'Talolong', 'Tamlong', 'Tongko', 'Valencia', 'Wakas'],
        },
    },
    'Mimaropa (Region IV-B)': {
        'Marinduque': {
            'Boac': ['Agot', 'Agumaymayan', 'Amoingon', 'Apitong', 'Balagasan', 'Balaring', 'Balimbing', 'Balogo', 'Bamban', 'Bangbangalon', 'Bantad', 'Bantay', 'Bayuti', 'Binunga', 'Boi', 'Boton', 'Buliasnin', 'Bunganay', 'Caganhao', 'Canat', 'Catubugan', 'Cawit', 'Daig', 'Daypay', 'Duyay', 'Hinapulan', 'Ihatub', 'Isok I', 'Isok II Pob.', 'Laylay', 'Lupac', 'Mahinhin', 'Mainit', 'Malbog', 'Maligaya', 'Malusak', 'Mansiwat', 'Mataas na Bayan', 'Maybo', 'Mercado', 'Murallon', 'Ogbac', 'Pawa', 'Pili', 'Poctoy', 'Poras', 'Puting Buhangin', 'Puyog', 'Sabong', 'San Miguel', 'Santol', 'Sawi', 'Tabi', 'Tabigue', 'Tagwak', 'Tambunan', 'Tampus', 'Tanza', 'Tugos', 'Tumagabok', 'Tumapon'],
            'Gasan': ['Antipolo', 'Bachao Ibaba', 'Bachao Ilaya', 'Bacongbacong', 'Bahi', 'Bangbang', 'Banot', 'Banuyo', 'Bognuyan', 'Cabugao', 'Dawis', 'Dili', 'Libtangin', 'Mahunig', 'Mangiliol', 'Masiga', 'Matandang Gasan', 'Pangi', 'Pingan', 'Tabionan', 'Tapuyan', 'Tiguion'],
            'Santa Cruz': ['Alobo', 'Angas', 'Aturan', 'Bagong Silang Pob.', 'Baguidbirin', 'Baliis', 'Balogo', 'Banogbog', 'Biga', 'Botilao', 'Buyabod', 'Dating Bayan', 'Devilla', 'Dolores', 'Haguimit', 'Hupi', 'Ipil', 'Jolo', 'Kaganhao', 'Kalangkang', 'Kamandugan', 'Kasily', 'Kilo-kilo', 'Kiñaman', 'Labonan', 'Lamesa', 'Landy', 'Libjo', 'Lusok', 'Maharlika Pob.', 'Matalaba', 'Napo', 'Pag-asa Pob.', 'Pantayin', 'Pili', 'Polo', 'Pulong-Parang', 'Punong', 'San Antonio', 'San Isidro', 'Tagum', 'Tamayo', 'Tambangan', 'Tawiran', 'Taytay'],
        },
        'Occidental Mindoro': {
            'San Jose': ['Ansiray', 'Bagong Sikat', 'Batong Dalig', 'Bayotbot', 'Bubog', 'Buri', 'Camburay', 'Caminawit', 'Catayungan', 'Central', 'Labangan Poblacion', 'Lagnas', 'Mangarin', 'Mapaya', 'Minsulao', 'Murtha', 'Natandol', 'Pag-asa', 'Palamponk', 'Pawican', 'San Agustin', 'San Isidro', 'San Vicente Central', 'San Vicente North', 'San Vicente South', 'San Vicente West', 'Santo Niño', 'Soloyoak', 'Wangal'],
            'Mamburao': ['Balansay', 'Balogo', 'Fatima', 'Gamay', 'Lapu-Lapu Pob.', 'Lumangbayan', 'Malibo', 'Manoot', 'Pambisan Bata', 'Pambisan Matanda', 'Payompon', 'Poblacion 1', 'Poblacion 2', 'Poblacion 3', 'Poblacion 4', 'Poblacion 5', 'San Fernando', 'Talabaan', 'Tangkalan', 'Tayamaan', 'Tigwi'],
        },
        'Oriental Mindoro': {
            'Calapan City': ['Balingayan', 'Balite', 'Baruyan', 'Batino', 'Bayanan I', 'Bayanan II', 'Biga', 'Bondoc', 'Bucayao', 'Buhuan', 'Bulusan', 'Camilmil', 'Canubing I', 'Canubing II', 'Comunal', 'Tawiran', 'Del Pilar', 'Duo', 'Evangelista', 'Guinobatan', 'Gulod', 'Gutad', 'Ibaba East', 'Ibaba West', 'Ilaya', 'Lalud', 'Lazareto', 'Libis', 'Lumang Bayan', 'Mahal na Pangalan', 'Malad', 'Malamig', 'Managpi', 'Masipit', 'Nag-iba I', 'Nag-iba II', 'Navotas', 'Pachoca', 'Palhi', 'Panggalaan', 'Parang', 'Patas', 'Personas', 'Poblacion', 'Putingtubig', 'Sabang', 'Salong', 'San Antonio', 'San Vicente Central', 'San Vicente East', 'San Vicente North', 'San Vicente South', 'San Vicente West', 'Santa Cruz', 'Santa Isabel', 'Santa Maria Village', 'Santa Rita', 'Santo Niño', 'Sapul', 'Silonay', 'Suqui', 'Tawiran', 'Tibag', 'Wawa'],
            'Puerto Galera': ['Aninuan', 'Balatero', 'Dulangan', 'Palangan', 'Poblacion', 'Sabang', 'San Antonio', 'San Isidro', 'Sinandigan', 'Tabinay', 'Villaflor'],
        },
        'Palawan': {
            'Puerto Princesa': ['Bagong Sikat', 'Bahile', 'Bancao-Bancao', 'Binduyan', 'Barangay Poblacion', 'Cabayugan', 'Inagawan', 'Irawan', 'Iwahig', 'Lucbuan', 'Macarascas', 'Malibi', 'Maningning', 'Manggahan', 'Marufinas', 'Maoyon', 'Napsan', 'New Panggangan', 'San Jose', 'San Manuel', 'San Miguel', 'San Pedro', 'San Rafael', 'Santa Cruz', 'Santa Lourdes', 'Santa Lucia', 'Santa Monica', 'Tagabinet', 'Tagburos', 'Tagumpay', 'Tanabag', 'Tiniguiban'],
            'Coron': ['Banuang Daan', 'Barangay I', 'Barangay II', 'Barangay III', 'Barangay IV', 'Barangay V', 'Barangay VI', 'Borac', 'Buenavista', 'Bulalacao', 'Cabugao', 'Decalachao', 'Guadalupe', 'Lajala', 'Malawig', 'Marcilla', 'San Jose', 'San Nicolas', 'Tagumpay', 'Tara', 'Turda'],
            'El Nido': ['Aberawan', 'Bagong Bayan', 'Barotuan', 'Bebeladan', 'Buena Suerte', 'Corong-Corong', 'Maligaya', 'Manlag', 'Masagana', 'Matawawe', 'New Ibajay', 'Pasadeña', 'Poblacion', 'San Fernando', 'Sibaltan', 'Teneguiban', 'Villa Libertad', 'Villa Paz'],
        },
        'Romblon': {
            'Romblon': ['Agbaluto', 'Agbudia', 'Agpanabat', 'Agnaga', 'Agnay', 'Agnipa', 'Agti', 'Agtongo', 'Bagacay', 'Cajimos', 'Calabogo', 'Capaclan', 'Ginablan', 'Ilauran', 'Lamao', 'Lumbang', 'Macalas', 'Mapula', 'Palja', 'Poblacion', 'Sawang', 'Sablayan', 'Tambac'],
            'Odiongan': ['Anahao', 'Bangon', 'Batiano', 'Budiong', 'Gabawan', 'Libertad', 'Liwanag', 'Liwayway', 'Matulatula', 'Mayha', 'Panique', 'Poblacion', 'Tabing Dagat', 'Tuburan', 'Tulay'],
        },
    },
    'Bicol (Region V)': {
        'Albay': {
            'Legazpi City': ['Bagong Abre', 'Banquerohan', 'Bitano', 'Bonot', 'Buenavista', 'Buragwis', 'Buyuan', 'Cabagan', 'Cruzada', 'Dinagaan', 'Dita', 'Estanza', 'Gogon', 'Homapon', 'Ilawod Pob.', 'Ilawod West', 'Imalnod', 'Kawit East', 'Kawit West', 'Lamba', 'Landang', 'Lapu-Lapu', 'Mabini', 'Mariawa', 'Padang', 'Pawa', 'Pigcale', 'Rawis', 'Sabang', 'San Francisco', 'San Joaquin', 'San Rafael', 'San Roque', 'Santa Cruz', 'Tagas', 'Tamaoyan', 'Taysan', 'Victory Village North', 'Victory Village South', 'Williamsburg'],
            'Tabaco City': ['Agnas', 'Bacolod', 'Bangkilingan', 'Bantayan', 'Bombon', 'Buang', 'Cabayugan', 'Cobo', 'Comon', 'Divino Rostro', 'Fatima', 'Habang', 'Magapo', 'Mariroc', 'Ombao Heights', 'Panal', 'Pawa', 'Pinagbobong', 'Poblacion District 1', 'Poblacion District 2', 'Poblacion District 3', 'Poblacion District 4', 'Quinale Cabasan', 'Quinastillojan', 'Rawis', 'Sagurong', 'Salugan', 'San Antonio', 'San Carlos', 'San Isidro', 'San Jose', 'San Juan', 'San Lorenzo', 'San Miguel', 'San Ramon', 'San Roque', 'San Vicente', 'Santa Cruz', 'Sto. Cristo', 'Sua-Igot', 'Tabiguian', 'Tagas', 'Tahao', 'Visita'],
            'Ligao City': ['Allang', 'Bagumbayan', 'Banao', 'Barayong', 'Batang', 'Binatagan', 'Bobonsuran', 'Bonga', 'Cabagñan', 'Cavasi', 'Cullat', 'Dunao', 'Herrera', 'Macabugos', 'Mahaba', 'Nasisi', 'Paulba', 'Paulog', 'Pinit', 'Poblacion', 'San Isidro', 'Santa Cruz', 'Tamlagon', 'Tastas', 'Tinago', 'Tomolin'],
        },
        'Camarines Norte': {
            'Daet': ['Alawihao', 'Bagasbas', 'Barangay I', 'Barangay II', 'Barangay III', 'Barangay IV', 'Barangay V', 'Barangay VI', 'Barangay VII', 'Barangay VIII', 'Bibirao', 'Borabod', 'Calasgasan', 'Cobangbang', 'Dogongan', 'Gahonon', 'Gubat', 'Lag-on', 'Mancruz', 'Mambalite', 'Pamorangon', 'San Isidro'],
            'Jose Panganiban': ['Bagong Bayan', 'Calero', 'Dahican', 'Dayhagan', 'Larap', 'Luklukan Norte', 'Luklukan Sur', 'North Poblacion', 'Osmeña', 'Parang', 'Plaridel', 'San Antonio', 'San Miguel', 'San Pedro', 'Santa Cruz', 'Santa Elena', 'Santa Milagrosa', 'South Poblacion', 'Tamisan'],
        },
        'Camarines Sur': {
            'Naga City': ['Abella', 'Bagumbayan Norte', 'Bagumbayan Sur', 'Balatas', 'Calauag', 'Cararayan', 'Carolina', 'Concepcion Grande', 'Concepcion Pequeña', 'Dayangdang', 'Del Rosario', 'Dinaga', 'Igualdad Interior', 'Lerma', 'Liboton', 'Mabolo', 'Pacol', 'Panicuason', 'Peñafrancia', 'Sabang', 'San Felipe', 'San Francisco', 'San Isidro', 'Santa Cruz', 'Tabuco', 'Tinago', 'Triangulo'],
            'Iriga City': ['Antipolo', 'Cristo Rey', 'Del Rosario', 'Francia', 'La Anunciacion', 'La Medalla', 'La Purisima', 'Las Navas', 'Niño Jesus', 'Perpetual Help', 'Sagrada', 'Salvacion', 'San Agustin', 'San Antonio', 'San Francisco', 'San Isidro', 'San Jose', 'San Juan', 'San Miguel', 'San Nicolas', 'San Pedro', 'San Rafael', 'San Ramon', 'San Roque', 'San Vicente Fundado', 'San Vicente Norte', 'San Vicente Sur', 'Santa Cruz Norte', 'Santa Cruz Sur', 'Santa Elena', 'Santa Isabel', 'Santa Maria', 'Santiago Centro', 'Santiago Norte', 'Santiago Sur', 'Santo Domingo', 'Santo Niño'],
            'Pili': ['Anayan', 'Bagong Sirang', 'Binanuaanan', 'Cadlan', 'Caroyroyan', 'Curry', 'Del Rosario', 'Himaao', 'La Purisima', 'New San Roque', 'Old San Roque', 'Palestina', 'Pawili', 'Poblacion Norte', 'Poblacion Sur', 'Sagurong', 'San Agustin', 'San Antonio', 'San Isidro', 'San Jose', 'San Juan', 'San Vicente', 'Santiago', 'Santo Niño', 'Tagbong', 'Tinangis'],
        },
        'Catanduanes': {
            'Virac': ['Antipolo', 'Balite', 'Batag', 'Calabnigan', 'Calatagan Proper', 'Calatagan Tibang', 'Concepcion Pequena', 'Concepcion Proper', 'Danicop', 'Dugui', 'Francia', 'Gogon Sirangan', 'Gogon Tibang', 'Hawan Ilaya', 'Hawan Interior', 'Igang', 'Magnesia Proper', 'Magnesia Tibang', 'Marilima', 'Palnab Proper', 'Palnab Tibang', 'Salvacion', 'San Isidro', 'San Jose', 'San Pedro', 'San Roque', 'San Vicente', 'Santa Cruz', 'Santa Elena', 'Santo Domingo', 'Talisay', 'Sogod'],
            'San Andres': ['Alibuag', 'As-Is', 'Benticayan', 'Calabagas', 'Espinosa', 'Esperanza', 'Guelac', 'Herederos', 'Hinipaan', 'Igang', 'Libod', 'Mabato', 'Mandaon', 'Poblacion', 'San Isidro', 'San Jose', 'San Roque', 'Santa Barbara', 'Talisay', 'Taragtag', 'Tomolin'],
        },
        'Masbate': {
            'Masbate City': ['Asid', 'Bapor', 'Batuhan', 'Benigno Aquino Jr.', 'Bolo', 'Centro', 'F. Magallanes', 'Ibingay', 'Igang', 'Kinamaligan', 'Malinta', 'Mapiña', 'Market Site', 'Nursery', 'Pating', 'Sinalongan', 'Usab'],
            'Mobo': ['Baang', 'Bagacay', 'Balawon', 'Bangad', 'Bantugan', 'Baras', 'Bolo', 'Butag', 'Cabitan', 'Dacu', 'Fabrica', 'Guintorelan', 'Luy-a', 'Mactan', 'Mapuyo', 'Marintoc', 'Ombao', 'Poblacion', 'Tabunan', 'Tayopon'],
        },
        'Sorsogon': {
            'Sorsogon City': ['Abuyog', 'Bacon', 'Balete', 'Balogo', 'Bañga', 'Basud', 'Bibincahan', 'Bucalbucal', 'Buenavista', 'Burabod', 'Cabid-an', 'Cambulaga', 'Capuy', 'Gimaloto', 'Macabog', 'Osiao', 'Panlayaan', 'Polvorista', 'Ponong', 'Rawis', 'Salog', 'San Juan', 'San Pascual', 'Sirangan', 'Sulucan', 'Talisay'],
            'Bulan': ['A. Bonifacio', 'Abad Santos', 'Antipolo', 'Bical', 'Biong', 'Butag', 'Calomagon', 'Daganas', 'Dapdap', 'E. Quirino', 'Fabrica', 'Gulang-Gulang', 'Imelda', 'Jamorawon', 'Lajong', 'Nasuje', 'Obrero', 'Otavi', 'Pag-asa', 'Poblacion', 'Sagrada', 'Salvacion', 'San Francisco', 'San Isidro', 'San Jose', 'San Rafael', 'San Ramon', 'San Roque', 'San Vicente', 'Santa Remedios', 'Tiwi'],
        },
    },
    'Western Visayas (Region VI)': {
        'Aklan': {
            'Kalibo': ['Andagao', 'Bachaw Norte', 'Bachaw Sur', 'Briones', 'Estancia', 'Mabilo', 'Mobo', 'Pook', 'Poblacion', 'Tigayon'],
            'Boracay': ['Balabag', 'Manoc-Manoc', 'Yapak'],
        },
        'Antique': {
            'San Jose de Buenavista': ['Agpipili', 'Bagacay', 'Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8', 'Barangay 9', 'Barangay 10', 'Dawis', 'Mayag', 'Nava', 'Progreso', 'San Fernando', 'San Juan', 'San Pedro', 'Sinamay', 'Suhi'],
        },
        'Capiz': {
            'Roxas City': ['Adlawan', 'Baybay', 'Bolo', 'Cabugao', 'Cogon', 'Culasi', 'Dinginan', 'Dumolog', 'Gabu-an', 'Jumaguicjic', 'Lapu-Lapu', 'Libas', 'Lonoy', 'Macarse', 'Milibili', 'Punta Tabuc', 'San Jose', 'Tanque', 'Tiza'],
        },
        'Guimaras': {
            'Jordan': ['Alaguisoc', 'Balcon Maravilla', 'Balcon Melliza', 'Bugnay', 'Poblacion', 'San Miguel', 'Santa Teresa', 'Tamborong'],
        },
        'Iloilo': {
            'Iloilo City': ['Arevalo District', 'Boulevard', 'Brgy. San Pedro', 'City Proper', 'Jaro', 'La Paz', 'Lapuz', 'Mandurriao', 'Molo', 'Tanza-Baybay', 'Zamora-Melliza'],
            'Passi City': ['Aglalana', 'Agdahon', 'Arac', 'Ayuyan', 'Bacuranan', 'Batu', 'Bitaogan', 'Cadilang', 'Carataya', 'Dalicanan', 'Gemat-y', 'Gines Viejo', 'Jaguimitan', 'Lumbo', 'Malusgod', 'Man-it', 'Mantulang', 'Pagdugue', 'Poblacion Ilawod', 'Poblacion Ilaya', 'Sablogon', 'Salag', 'Salngan', 'Sariri'],
        },
        'Negros Occidental': {
            'Bacolod City': ['Alijis', 'Alangilan', 'Banago', 'Barangay 1', 'Barangay 2', 'Bata', 'Estefania', 'Granada', 'Handumanan', 'Mandalagan', 'Mansilingan', 'Pahanocoy', 'Punta Taytay', 'Singcang-Airport', 'Sum-ag', 'Taculing', 'Tangub', 'Villamonte', 'Vista Alegre'],
            'Silay City': ['Bagtic', 'Balaring', 'Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Eustaquio Lopez', 'Guimbala-on', 'Guinhalaran', 'Hawaiian', 'Kapitan Ramon', 'Lantad', 'Mambulac', 'Patag', 'Rizal'],
            'Talisay City': ['Cabatangan', 'Dos Hermanas', 'Katilingban', 'Matab-ang', 'Poblacion', 'San Fernando', 'Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7', 'Zone 8', 'Zone 9', 'Zone 10', 'Zone 11', 'Zone 12', 'Zone 13', 'Zone 14'],
        },
    },
    'Central Visayas (Region VII)': {
        'Bohol': {
            'Tagbilaran City': ['Bool', 'Booy', 'Cabawan', 'Cogon', 'Dampas', 'Dao', 'Manga', 'Mansasa', 'Poblacion I', 'Poblacion II', 'Poblacion III', 'San Isidro', 'Taloto', 'Tiptip', 'Ubujan'],
            'Ubay': ['Achila', 'Bay-ang', 'Biabas', 'Bongbong', 'Buenavista', 'Cagting', 'Camambugan', 'Cuya', 'Fatima', 'Gabi', 'Governor Boyles', 'Guintabo-an', 'Hambabauran', 'Humayhumay', 'Ilihan', 'Juagdan', 'Katarungan', 'Kinabag-an', 'Lomangog', 'Lumbocan', 'Mapawa', 'Poblacion', 'San Francisco', 'San Pascual', 'San Vicente', 'Santa Cruz', 'Santo Niño', 'Sentinela', 'Tapal', 'Tapon', 'Tintinan', 'Tipolo', 'Trinidad', 'Union', 'Yanangan'],
        },
        'Cebu': {
            'Cebu City': ['Apas', 'Banilad', 'Basak Pardo', 'Basak San Nicolas', 'Busay', 'Campu Tisa', 'Capitol Site', 'Cogon Ramos', 'Day-as', 'Guadalupe', 'Kasambagan', 'Labangon', 'Lahug', 'Mabolo', 'Malubog', 'Pahina Central', 'Pardo', 'Pasil', 'Pit-os', 'Poblacion Pardo', 'Pung-ol-Sibugay', 'Punta Princesa', 'Sambag I', 'Sambag II', 'San Antonio', 'San Jose', 'San Nicolas Central', 'Santa Cruz', 'Suba', 'Talamban', 'Tisa', 'Zapatera'],
            'Mandaue City': ['Alang-Alang', 'Bakilid', 'Banilad', 'Basak', 'Cabancalan', 'Cambaro', 'Canduman', 'Casili', 'Casuntingan', 'Centro', 'Cubacub', 'Guizo', 'Ibabao-Estancia', 'Jagobiao', 'Labogon', 'Looc', 'Maguikay', 'Mantuyong', 'Opao', 'Pagsabungan', 'Pakna-an', 'Subangdaku', 'Tabok', 'Tawason', 'Tingub', 'Tipolo', 'Umapad'],
            'Lapu-Lapu City': ['Agus', 'Babag', 'Bankal', 'Baring', 'Basak', 'Buaya', 'Canjulao', 'Caw-oy', 'Caubian', 'Cawhagan', 'Gun-ob', 'Ibo', 'Looc', 'Mactan', 'Maribago', 'Marigondon', 'Pajac', 'Pangan-an', 'Poblacion', 'Pajo', 'Pusok', 'Sabang', 'Santa Rosa', 'Subabasbas', 'Talima', 'Tingo', 'Tungasan'],
        },
        'Negros Oriental': {
            'Dumaguete City': ['Bagacay', 'Bajumpandan', 'Balugo', 'Banilad', 'Bantayan', 'Batinguel', 'Bunao', 'Cadawinonan', 'Calceta', 'Camanjac', 'Candau-ay', 'Cantil-e', 'Daro', 'Junob', 'Looc', 'Mangnao-Canal', 'Motong', 'Piapi', 'Poblacion No. 1', 'Poblacion No. 2', 'Poblacion No. 3', 'Poblacion No. 4', 'Poblacion No. 5', 'Poblacion No. 6', 'Poblacion No. 7', 'Poblacion No. 8', 'Pulantubig', 'Tabuc-tubig', 'Taclobo', 'Talay'],
            'Bais City': ['Barangay I', 'Barangay II', 'Barangay III', 'Cabanlutan', 'Cambagahan', 'Canlargo', 'Hangyad', 'La Paz', 'Mabunao', 'Manjuyod', 'Okiot', 'Olympia', 'Panala-an', 'Poblacion', 'Sal-oc', 'Sab-ahan', 'San Jose', 'Tamisu', 'Tangculogan', 'Villacayo'],
        },
        'Siquijor': {
            'Siquijor': ['Bandila Norte', 'Bandila Sur', 'Bolo-bolo', 'Caipilan', 'Caitican', 'Calalinan', 'Calunasan Norte', 'Calunasan Sur', 'Candaping', 'Cangmangki', 'Cangomantong', 'Cansibugan', 'Dumanhog', 'Lapong', 'Libo', 'Luyang', 'Napo', 'Nug-as', 'Olang', 'Panlautan', 'Poblacion', 'Ponong', 'Sandugan', 'Songculan', 'Tambisan', 'Tinago', 'Tongo-anon'],
        },
    },
    'Eastern Visayas (Region VIII)': {
        'Biliran': {
            'Naval': ['Anibong', 'Atipolo', 'Cabucgayan', 'Calumpang', 'Capiñahan', 'Libtong', 'Mabini', 'Padre Inocentes Garcia', 'Poblacion', 'Sabang', 'San Pablo', 'Santissimo Rosario', 'Santo Niño', 'Villa Caneja', 'Villa Consuelo'],
        },
        'Eastern Samar': {
            'Borongan': ['Alang-alang', 'Amantacop', 'Ando', 'Balacdas', 'Balud', 'Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Benowangan', 'Banuyo', 'Bugas', 'Cabalagnan', 'Cabong', 'Cagbonga', 'Calico-an', 'Canjaway', 'Canlaray', 'Divinubo', 'Hebobawis', 'Hindang', 'Lalawigan', 'Libuton', 'Locso-on', 'Maybacong', 'Maypangdan', 'Panda', 'Pinanag-an', 'Poblacion Barangay 7', 'Poblacion Barangay 8', 'Sabang North', 'Sabang South', 'San Gabriel', 'San Jose', 'San Pablo', 'Santa Fe', 'Songco', 'Suribao', 'Tabunan', 'Taboc', 'Tara-an'],
        },
        'Leyte': {
            'Tacloban City': ['Abucay', 'Bagacay', 'Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 88', 'Barangay 89', 'Barangay 90', 'Barangay 103', 'Barangay 104', 'Barangay 107', 'Barangay 109', 'Diit', 'Marasbaras', 'New Kawayan', 'Old Kawayan', 'Palanog', 'Sagkahan', 'San Jose', 'San Roque', 'Santa Elena', 'Santo Niño', 'Suhi', 'V & G Subdivision'],
            'Ormoc City': ['Alegria', 'Alta Vista', 'Bagong', 'Bantigue', 'Batuan', 'Biliboy', 'Bog-o', 'Cabulihan', 'Camp Downes', 'Can-adieng', 'Catmon', 'Cogon Combado', 'Concepcion', 'Culaba', 'Danao', 'Dolores', 'Donghol', 'District 1', 'District 2', 'District 3', 'District 4', 'District 5', 'District 6', 'District 7', 'District 8', 'District 9', 'District 10', 'District 11', 'District 12', 'District 13', 'District 14', 'District 15', 'District 16', 'District 17', 'District 18', 'District 19', 'District 20', 'District 21', 'District 22', 'District 23', 'District 24', 'District 25', 'District 26', 'District 27', 'District 28', 'District 29', 'Liberty', 'Linao', 'Mabini', 'Margen', 'Naungan', 'Rufina M. Tan', 'Salvacion', 'San Antonio', 'San Isidro', 'San Jose', 'San Juan', 'San Pablo', 'Santo Niño', 'Valencia'],
        },
        'Northern Samar': {
            'Catarman': ['Acacia', 'Aguinaldo', 'Airport Village', 'Bangkerohan', 'Baybay', 'Bocsol', 'Calachuchi', 'Cawayan', 'Dag-am', 'Dalakit', 'Gebulwangan', 'Imelda', 'Jose Abad Santos', 'Lapu-lapu', 'Mabini', 'Macagtas', 'McKinley', 'New Rizal', 'Old Rizal', 'Pasali', 'Polangi', 'Quezon', 'Salvacion', 'San Pascual', 'Somoge', 'Talisay', 'Trangue', 'Washington'],
        },
        'Samar': {
            'Catbalogan': ['Basud', 'Bunuanan', 'Canlapwas', 'Estaka', 'Guinsorongan', 'Ibol', 'Lagundi', 'Mancol', 'Mercedes', 'Payao', 'Poblacion 1', 'Poblacion 2', 'Poblacion 3', 'San Andres', 'San Pablo'],
        },
        'Southern Leyte': {
            'Maasin City': ['Abgao', 'Acasia', 'Asuncion', 'Bactul', 'Badiang', 'Baliw', 'Banahao', 'Bantigue', 'Bataan', 'Bato', 'Baugo', 'Bilibol', 'Bogo', 'Cabulisan', 'Cagnituan', 'Canturing', 'Combado', 'Dongon', 'Imelda', 'Lonoy', 'Lunas', 'Manhilo', 'Mantahan', 'Matin-ao', 'Nati', 'Pasay', 'Pongso', 'Tagnipa', 'Tam-is', 'Tawid', 'Tigbao', 'Tunga-tunga'],
        },
    },
    'Zamboanga Peninsula (Region IX)': {
        'Zamboanga del Norte': {
            'Dipolog City': ['Barra', 'Biasong', 'Central', 'Cogon', 'Dicayas', 'Estaka', 'Galas', 'Gulayon', 'Lugdungan', 'Miputak', 'Olingan', 'Poblacion', 'San Jose', 'Santa Filomena', 'Santa Isabel', 'Sicayab', 'Sinaman', 'Turno'],
            'Dapitan City': ['Alagar', 'Bagting', 'Banonong', 'Barcelona', 'Baylimango', 'Canlucani', 'Dapitan City', 'Ilaya', 'Maria Uray', 'Masidlakon', 'Napo', 'Opao', 'Polo', 'Potol', 'San Francisco', 'San Nicolas', 'San Vicente', 'Santa Cruz', 'Sicayab', 'Taguilon'],
        },
        'Zamboanga del Sur': {
            'Pagadian City': ['Alegria', 'Balangasan', 'Balintawak', 'Baloyboan', 'Banale', 'Bogo', 'Bomba', 'Bulatok', 'Dampalan', 'Danlugan', 'Dao-dao', 'Datagan', 'Deborok', 'Dumagoc', 'Gatas District', 'Gubac', 'Gubawan', 'Kagawasan', 'Kahayagan', 'Kalasan', 'Kawit', 'La Suerte', 'Lala', 'Lapidian', 'Lenienza', 'Lizon', 'Lower Sibatang', 'Lumbia', 'Luyahan', 'Macasing', 'Manaon', 'Muricay', 'Napolan', 'Palpalan', 'Pedulonan', 'Poloyagan', 'San Jose', 'San Pedro', 'Santa Lucia', 'Santa Maria', 'Santiago', 'Santo Niño', 'Tawagan Norte', 'Tiguma', 'Tuburan', 'Tulangan', 'Tulawas', 'Upper Sibatang', 'White Beach'],
        },
        'Zamboanga Sibugay': {
            'Ipil': ['Bacalan', 'Bangkerohan', 'Caparan', 'Doña Josefa', 'Guituan', 'Lourdes', 'Lumbia', 'Magdaup', 'Poblacion', 'San Jose', 'Sanito', 'Taway', 'Tenan', 'Tomitom', 'Tuwit'],
        },
    },
    'Northern Mindanao (Region X)': {
        'Bukidnon': {
            'Malaybalay City': ['Aglayan', 'Bangcud', 'Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8', 'Barangay 9', 'Barangay 10', 'Busdi', 'Cabangahan', 'Caburacanan', 'Casisang', 'Dalwangan', 'Imbayao', 'Kalasungay', 'Linabo', 'Managok', 'Magsaysay', 'Patpat', 'San Jose', 'San Martin', 'Sinanglanan', 'Sumpong', 'Zamboanguita'],
            'Valencia City': ['Bagontaas', 'Banlag', 'Batangan', 'Barobo', 'Catumbalon', 'Colonia', 'Guinoyuran', 'Lilingayon', 'Lourdes', 'Lumbo', 'Mailag', 'Maramag', 'Nabago', 'Pinatilan', 'Poblacion', 'San Carlos', 'Sinayawan', 'Sugod', 'Tongantongan', 'Tugaya'],
        },
        'Camiguin': {
            'Mambajao': ['Agoho', 'Anito', 'Baylao', 'Balbagon', 'Benoni', 'Bura', 'Kuguita', 'Naasag', 'Pandan', 'Poblacion', 'Soro-soro', 'Tapong', 'Tupsan', 'Yumbing'],
        },
        'Lanao del Norte': {
            'Iligan City': ['Abuno', 'Acmac', 'Bagong Silang', 'Bunawan', 'Buru-un', 'Dalipuga', 'Del Carmen', 'Digkilaan', 'Ditucalan', 'Dulag', 'Hinaplanon', 'Hindang', 'Kalilangan', 'Kiwalan', 'Lanipao', 'Luinab', 'Mahayahay', 'Mandulog', 'Maria Cristina', 'Palao', 'Poblacion', 'Puga-an', 'Rogongon', 'San Miguel', 'San Roque', 'Santiago', 'Santo Rosario', 'Saray', 'Suarez', 'Tambacan', 'Tibanga', 'Tipanoy', 'Tominobo', 'Tomas L. Cabili', 'Tubod', 'Ubaldo Laya', 'Upper Hinaplanon', 'Villa Verde'],
        },
        'Misamis Occidental': {
            'Oroquieta City': ['Apil', 'Binuangan', 'Bolibol', 'Buenavista', 'Bunga', 'Canubay', 'Clarin Settlement', 'Dolipos Bajo', 'Dolipos Alto', 'Dulapo', 'Langcangan Bajo', 'Langcangan Proper', 'Layawan', 'Lobogon', 'Lower Langcangan', 'Malindang', 'Mialen', 'Mobod', 'Poblacion I', 'Poblacion II', 'Pines', 'Rizal', 'San Vicente', 'Taboc Norte', 'Taboc Sur', 'Talairon', 'Tipan', 'Toliyok', 'Tuburan', 'Upper Langcangan', 'Villaflor'],
        },
        'Misamis Oriental': {
            'Cagayan de Oro City': ['Agusan', 'Balubal', 'Balulang', 'Baikingon', 'Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5', 'Barangay 6', 'Barangay 7', 'Barangay 8', 'Barangay 9', 'Barangay 10', 'Barangay 11', 'Barangay 12', 'Barangay 13', 'Barangay 14', 'Barangay 15', 'Barangay 16', 'Barangay 17', 'Barangay 18', 'Barangay 19', 'Barangay 20', 'Barangay 21', 'Barangay 22', 'Barangay 23', 'Barangay 24', 'Barangay 25', 'Barangay 26', 'Barangay 27', 'Barangay 28', 'Barangay 29', 'Barangay 30', 'Barangay 31', 'Barangay 32', 'Barangay 33', 'Barangay 34', 'Barangay 35', 'Barangay 36', 'Barangay 37', 'Barangay 38', 'Barangay 39', 'Barangay 40', 'Bayabas', 'Bayanga', 'Bonbon', 'Bugo', 'Bulua', 'Camaman-an', 'Canito-an', 'Carmen', 'Consolacion', 'Cugman', 'Dansolihon', 'F.S. Catanico', 'Gusa', 'Indahag', 'Iponan', 'Kauswagan', 'Lapasan', 'Lumbia', 'Macabalan', 'Macasandig', 'Mambuaya', 'Nazareth', 'Pagalungan', 'Pagatpat', 'Patag', 'Pigsag-an', 'Puerto', 'Puntod', 'San Simon', 'Tablon', 'Taglimao', 'Tagpangi', 'Tignapoloan', 'Tuburan'],
        },
    },
    'Davao (Region XI)': {
        'Davao del Norte': {
            'Tagum City': ['Apokon', 'Bincungan', 'Busaon', 'Canocotan', 'Cuambogan', 'La Filipina', 'Liboganon', 'Madaum', 'Magugpo East', 'Magugpo North', 'Magugpo Poblacion', 'Magugpo South', 'Magugpo West', 'Mankilam', 'New Balamban', 'Nueva Fuerza', 'Pagsabangan', 'Pandapan', 'San Agustin', 'San Isidro', 'Visayan Village'],
        },
        'Davao del Sur': {
            'Digos City': ['Aplaya', 'Balabag', 'Binaton', 'Colorado', 'Cogon', 'Dawis', 'Dulangan', 'Goma', 'Igpit', 'Kiagot', 'Lungag', 'Mahayahay', 'Matti', 'Ruparan', 'San Jose', 'San Miguel', 'Tiguman', 'Zone I', 'Zone II', 'Zone III'],
        },
        'Davao Oriental': {
            'Mati': ['Badas', 'Bobon', 'Buso', 'Cabuaya', 'Central', 'Culian', 'Dahican', 'Danao', 'Dawan', 'Don Enrique Lopez', 'Don Martin Marundan', 'Don Salvador Lopez Sr.', 'Langka', 'Lawigan', 'Libudon', 'Luban', 'Macambol', 'Mamali', 'Matiao', 'Mayo', 'Sainz', 'Sanghay', 'Tagabakid', 'Tagbinonga', 'Taguibo', 'Tamisan'],
        },
        'Davao de Oro': {
            'Nabunturan': ['Anislagan', 'Antequera', 'Basak', 'Bayabas', 'Bukal', 'Cabacungan', 'Cabidianan', 'Katipunan', 'Libasan', 'Linda', 'Magading', 'Magsaysay', 'Mainit', 'Manat', 'Matilo', 'Mipangi', 'Nabunturan Proper', 'Ogao', 'Pangutosan', 'Poblacion', 'San Isidro', 'San Roque', 'San Vicente', 'Santo Niño', 'Sasa', 'Tagnocon'],
        },
        'Davao Occidental': {
            'Malita': ['Bito', 'Bolila', 'Buhangin', 'Culaman', 'Datu Daligasao', 'Demoloc', 'Felis', 'Fishing Village', 'Kibalatong', 'Kinangan', 'Lacaron', 'Lagumit', 'Lais', 'Little Baguio', 'Mabini', 'Manuel Peralta', 'New Argao', 'Pangian', 'Pinalpalan', 'Poblacion', 'Sangay', 'Talogoy', 'Tical', 'Tulogan'],
        },
    },
    'Soccsksargen (Region XII)': {
        'Cotabato': {
            'Kidapawan City': ['Amas', 'Amazion', 'Balabag', 'Balindog', 'Binoligan', 'Gayola', 'Ginatilan', 'Indangan', 'Junction', 'Kalaisan', 'Lanao', 'Luvimin', 'Macabolig', 'Magsaysay', 'Malire', 'Manongol', 'Mateo', 'Mua-an', 'New Bohol', 'Nuangan', 'Onica', 'Perez', 'Poblacion', 'San Isidro', 'San Roque', 'Santo Niño', 'Sibawan', 'Sikitan', 'Singao', 'Sudapin'],
        },
        'Sarangani': {
            'Alabel': ['Bagacay', 'Baluntay', 'Datal Anggas', 'Kawas', 'Ladol', 'Maribulan', 'Pag-asa', 'Poblacion', 'Spring', 'Tokawal'],
        },
        'South Cotabato': {
            'General Santos City': ['Apopong', 'Baluan', 'Batomelong', 'Buayan', 'Bula', 'Calumpang', 'City Heights', 'Conel', 'Dadiangas East', 'Dadiangas North', 'Dadiangas South', 'Dadiangas West', 'Fatima', 'Katangawan', 'Labangal', 'Lagao', 'Ligaya', 'Mabuhay', 'Olympog', 'San Isidro', 'San Jose', 'Siguel', 'Sinawal', 'Tambler', 'Tinagacan', 'Upper Labay'],
            'Koronadal City': ['Assumption', 'Avanceña', 'Cacub', 'Caloocan', 'Carpenter Hill', 'Concepcion', 'Esperanza', 'General Paulino Santos', 'Magsaysay', 'Mambucal', 'Morales', 'Namnama', 'New Pangasinan', 'Paraiso', 'Rotonda', 'San Isidro', 'San Jose', 'San Roque', 'Saravia', 'Sarabia', 'Topland', 'Zone I', 'Zone II', 'Zone III', 'Zone IV'],
        },
        'Sultan Kudarat': {
            'Tacurong City': ['Baras', 'Buenaflor', 'Calean', 'Carmen', 'D. Montilla', 'Griño', 'Kalandagan', 'Lancheta', 'Lower Katungal', 'New Isabela', 'New Lagao', 'Poblacion', 'San Antonio', 'San Emmanuel', 'San Pablo', 'Tina', 'Upper Katungal'],
        },
    },
    'Caraga (Region XIII)': {
        'Agusan del Norte': {
            'Butuan City': ['Agao Poblacion', 'Agusan Pequeño', 'Ambago', 'Amparo', 'Ampayon', 'Anticala', 'Antongalon', 'Aupagan', 'Baan KM 3', 'Baan Riverside', 'Babag', 'Bading Poblacion', 'Bancasi', 'Banza', 'Bayanihan Poblacion', 'Bilay', 'Bit-os', 'Bitan-agan', 'Bobon', 'Bonbon', 'Bugabus', 'Bugsukan', 'Buhangin Poblacion', 'Cabcabon', 'Camayahan', 'Dagohoy Poblacion', 'Dankias', 'De Oro', 'Diego Silang Poblacion', 'Don Francisco', 'Doongan', 'Dulag', 'Dumalagan', 'Florida', 'Golden Ribbon Poblacion', 'Holy Redeemer Poblacion', 'Humabon Poblacion', 'Imadejas Poblacion', 'Jose Rizal Poblacion', 'Kinamlutan', 'Lapu-lapu Poblacion', 'Lemon', 'Leon Kilat Poblacion', 'Libertad', 'Limaha Poblacion', 'Los Angeles', 'Lumbocan', 'Maguinda', 'Mahay', 'Mahogany Poblacion', 'Maibu', 'Mandamo', 'Manila de Bugabus', 'Maon Poblacion', 'Masao', 'Maug', 'New Society Village Poblacion', 'Nong-Nong', 'Obrero Poblacion', 'Ong Yiu Poblacion', 'Pianing', 'Pinamanculan', 'Port Poyohon Poblacion', 'Rajah Soliman Poblacion', 'San Ignacio Poblacion', 'San Mateo', 'San Vicente', 'Sikatuna Poblacion', 'Silongan Poblacion', 'Sumile', 'Sumilihon', 'Tagabaca', 'Taguibo', 'Taligaman', 'Tandang Sora Poblacion', 'Tiniwisan', 'Tungao', 'Urduja Poblacion', 'Villa Kananga', 'Washington Poblacion'],
        },
        'Agusan del Sur': {
            'Bayugan': ['Anahaw', 'Berseba', 'Bucac', 'Caasinan', 'Cabantao', 'Cagbas', 'Charito', 'Claro Cortez', 'Ebro', 'Grace Estate', 'Hamogaway', 'Katipunan', 'Kinabjangan', 'Labao', 'Marcelina', 'Mt. Ararat', 'Mt. Carmel', 'Poblacion', 'Sagmone', 'Saguma', 'San Agustin', 'San Isidro', 'San Juan', 'San Roque', 'Taglatawan', 'Umayam', 'Villa Undayon', 'Wawa'],
        },
        'Dinagat Islands': {
            'San Jose': ['Aurelio', 'Bayanihan', 'Don Paulino', 'Don Ruben', 'Jacquez', 'Legaspi', 'Luna', 'Mabini', 'Maharlika', 'Magsaysay', 'Poblacion', 'San Jose', 'Santa Cruz', 'Santa Fe', 'White Beach'],
        },
        'Surigao del Norte': {
            'Surigao City': ['Alang-alang', 'Alegria', 'Anomar', 'Balibayon', 'Bilabid', 'Bonifacio', 'Canlanipa', 'Cantiasay', 'Catadman', 'Danao', 'Day-asan', 'Ipil', 'Luna', 'Mabini', 'Mabua', 'Mapawa', 'Nabago', 'Poblacion', 'Poctoy', 'Quezon', 'Rizal', 'San Juan', 'San Roque', 'Santa Cruz', 'Sidlakan', 'Silop', 'Sugbay', 'Taft', 'Togbongon', 'Trinidad', 'Washington'],
        },
        'Surigao del Sur': {
            'Tandag': ['Awasian', 'Bah-Bah', 'Bag-ong Lungsod', 'Bioto', 'Bongtud', 'Dagocdoc', 'Mabua', 'Malixi', 'Pandanon', 'Pangi', 'Poblacion', 'Quezon', 'Rosario', 'Salvacion', 'San Agustin Norte', 'San Agustin Sur', 'San Antonio', 'San Isidro', 'Telaje'],
        },
    },
    'BARMM (Bangsamoro)': {
        'Basilan': {
            'Isabela City': ['Aguada', 'Baluno', 'Begang', 'Binuangan', 'Busay', 'Cabunbata', 'Calvario', 'Carbon', 'Isabela Eastside', 'Kapatagan', 'Kumalarang', 'La Piedad', 'Lampinigan', 'Lukbuton', 'Maganda', 'Malagutay', 'Maligue', 'Menzi', 'Port Area', 'San Rafael', 'Santa Barbara', 'Small Kapatagan', 'Sumagdang', 'Tabiawan', 'Tampalan', 'Veteran\'s Village'],
        },
        'Lanao del Sur': {
            'Marawi City': ['Bangco', 'Basak Malutlut', 'Bato Ali Datu', 'Boganga', 'Bubonga Lilod Madaya', 'Bubonga Pagalamatan', 'Bubonga Ranao Cadingilan', 'Buadi Amunta', 'Buadi Arorao', 'Buadi Sacayo', 'Cabasaran', 'Cabingan', 'Cadayonan', 'Cadingilan', 'Calocan East', 'Calocan West', 'Dayawan', 'Datu Naga', 'Datu Sa Dansalan', 'Dansalan', 'Emie Punud', 'East Basak', 'Gadongan', 'Guimba', 'Kilala', 'Lilod Madaya', 'Lilod Saduc', 'Lomidong', 'Lumbac Madaya', 'Lumbac Marinaut', 'Malimono', 'Mapandi', 'Marinaut East', 'Marinaut West', 'Marawi Poblacion', 'Norhaya Village', 'Pagalamatan Gambai', 'Pagayawan', 'Panggao Saduc', 'Papandayan', 'Papandayan Caniogan', 'Patani', 'Pindolonan', 'Pindolonan Proper', 'Poona Marantao', 'Rapasun MSU', 'Raya Madaya I', 'Raya Madaya II', 'Raya Saduc', 'Rorogagus East', 'Rorogagus Proper', 'Saduc Proper', 'Sagayo Proper', 'Sagonsongan', 'Sangcay Dansalan', 'South Madaya Proper', 'Timbangalan', 'Toros', 'Tuca Ambolong', 'Tolali', 'Tuca Marinaut', 'Wawalayan Calocan', 'Wawalayan Marinaut'],
        },
        'Maguindanao': {
            'Cotabato City': ['Bagua I', 'Bagua II', 'Bagua III', 'Kalanganan I', 'Kalanganan II', 'Poblacion I', 'Poblacion II', 'Poblacion III', 'Poblacion IV', 'Poblacion V', 'Poblacion VI', 'Poblacion VII', 'Poblacion VIII', 'Poblacion IX', 'Rosary Heights I', 'Rosary Heights II', 'Rosary Heights III', 'Rosary Heights IV', 'Rosary Heights V', 'Rosary Heights VI', 'Rosary Heights VII', 'Rosary Heights VIII', 'Rosary Heights IX', 'Rosary Heights X', 'Rosary Heights XI', 'Rosary Heights XII', 'Rosary Heights XIII', 'Tamontaka I', 'Tamontaka II', 'Tamontaka III', 'Tamontaka IV', 'Tamontaka V'],
        },
        'Sulu': {
            'Jolo': ['Alat', 'Asturias', 'Bus-bus', 'Chinese Pier', 'Dato Amilbangsa Habit', 'Kanaway', 'Liang', 'Lower Sinangkapan', 'Poblacion', 'San Raymundo', 'Takut-Takut', 'Tandu-Banak', 'Tulay', 'Walled City'],
        },
        'Tawi-Tawi': {
            'Bongao': ['Bongao Poblacion', 'Ipil', 'Karungdong', 'Kamagong', 'Lakit-Lakit', 'Lamion', 'Lapid-Lapid', 'Lato-Lato', 'Luuk Pandan', 'Malassa', 'Mandulan', 'Masantong', 'Montay Montay', 'Nalil', 'Pababag', 'Pagasinan', 'Pahut', 'Pakigsing', 'Paniongan', 'Panunsulan', 'Pasiagan', 'Sanga-Sanga', 'Simandagit', 'Sumangday', 'Tarawakan', 'Tongsinah', 'Tubig-Boh'],
        },
    },
};

// Export raw address data for direct access
export { addressData };

export const philippineAddressData = {
    regions: Object.keys(addressData),
    
    getProvincesByRegion(region: string): string[] {
        if (!addressData[region]) return [];
        return Object.keys(addressData[region]);
    },
    
    getCitiesByProvince(province: string): string[] {
        for (const region in addressData) {
            if (addressData[region][province]) {
                return Object.keys(addressData[region][province]);
            }
        }
        return [];
    },
    
    getBarangaysByCity(province: string, city: string): string[] {
        for (const region in addressData) {
            if (addressData[region][province] && addressData[region][province][city]) {
                return addressData[region][province][city];
            }
        }
        return [];
    }
};
