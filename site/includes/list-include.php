<?php 

$objects = [
    (object)[
        'title' => 'Renan Plant',
        'description' => 'Descrição Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed justo.'
    ],
    (object)[
        'title' => 'Renan Plant',
        'description' => 'Descrição Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed justo.'
    ],
    (object)[
        'title' => 'Renan Plant',
        'description' => 'Descrição Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed justo.'
    ],
    (object)[
        'title' => 'Renan Plant',
        'description' => 'Descrição Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed justo.'
    ],
    (object)[
        'title' => 'Renan Plant',
        'description' => 'Descrição Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed justo.'
    ]
];

?>
<div class="list-page-container">
    <div class="list-page-content">
        <div class="location-container">
            <p class="location-text">
                Você está em:
            </p>
            <h1 id="location">
                URI Plantas
            </h1>
        </div>
        <section class="plant-list"> <!-- lista plantas -->
            <?php foreach($objects as $object){ ?>
            <div class="plant-box">
                <div class="plant-info">
                    <h1><?= $object->title;?></h1>
                    <p><?= $object->description;?></p>
                </div>
                <div class="plant-photo">
                    <img src="https://picsum.photos/536/354" alt="Foto da planta">
                </div>
            </div>
            <?php }?>
        </section>
    </div>
</div>