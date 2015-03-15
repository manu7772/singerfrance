jQuery(document).ready(function($) {

	////////////////////////////////////////////////////////
	// gestion des tabs fiche avis user sur article
	////////////////////////////////////////////////////////
	var checkTabcliks = function() {
		$("#produit_fiche_article_menu .tabcliks").each(function() {
			var targ = $(">span", $(this)).first().attr('id').replace("tab", "");
			if($(this).hasClass("btn_active")) {
				$("#"+targ).fadeIn(100);
			} else {
				$("#"+targ).fadeOut(100);
			}
		});
	}
	$("body").on("mouseenter", "#produit_fiche_article_menu .tabcliks", function() {
		if(!$(this).hasClass('btn_active')) {
			$("#produit_fiche_article_menu .tabcliks").removeClass('btn_active');
			$(this).addClass('btn_active');
			checkTabcliks();
		}
	});
	checkTabcliks();

	////////////////////////////////////////////////////////
	// gestion diaporamas
	////////////////////////////////////////////////////////
	$('#featured').orbit({
		animation: 'horizontal-push',		// fade, horizontal-slide, vertical-slide, horizontal-push
		animationSpeed: 400,				// how fast animtions are
		timer: true,						// true or false to have the timer
		advanceSpeed: parseInt($('#featured').attr('data-speed')),					// if timer is enabled, time between transitions 
		pauseOnHover: false,				// if you hover pauses the slider
		startClockOnMouseOut: false,		// if clock should start on MouseOut
		startClockOnMouseOutAfter: 1000,	// how long after MouseOut should the timer start again
		directionalNav: false,				// manual advancing directional navs
		captions: false,					// do you want captions?
		captionAnimation: 'fade',			// fade, slideOpen, none
		captionAnimationSpeed: 800,			// if so how quickly should they animate in
		bullets: true,						// true or false to activate the bullet navigation
		bulletThumbs: true,					// thumbnails for the bullets
		bulletThumbLocation: '',			// location from this file where thumbs will be
		afterSlideChange: function(){}		// fonction déclenchée après chaque changement de slide 
	});

	////////////////////////////////////////////////////////
	//
	////////////////////////////////////////////////////////
	var chlinit = $(".current").first();
	if(chlinit.length) {
		var ch_link = chlinit.attr("id").split("_");
		if(ch_link[0] == "tagchoice") {
			var ch_target = $("#targetchoice");
			if(ch_target.length) {
				var ch_html = $("#replacechoice_"+ch_link[1]).html();
				ch_target.html(ch_html);
			}
		}
	}
	$("body").on("click", ".tagchoice", function(event) {
		target = $("#targetchoice");
		if(target.length) {
			link = $(this).attr("id").split("_");
			if(link[0] == "tagchoice") {
				html = $("#replacechoice_"+link[1]).html();
				if(link[1].length) {
					$(".current").removeClass("current");
					$(this).addClass("current");
					target.html(html);
					event.preventDefault();
					return false;
				}
			}
		}
	});

	////////////////////////////////////////////////////////
	// Votes étoiles
	////////////////////////////////////////////////////////
	$("body").on('click', '.etoiles .note', function(event) {
		info = $(this).attr("data-prototype").split("__");
		if($(info[2]).data("vote") == undefined) { // <-- pour palier à la superposition des balises dans "etoiles"
			$(info[2]).data("vote", "ok");
			// $(info[2]).html('<p class="vote" style="margin:0px;padding:0px;height:15px;line-height:15px;">Merci !</p>');
			ConsoleResult("Note : ", info[1] + " pour l'article " + info[0], true);
			$("#noteArticle").val(info[1]);
			url = $(info[2]).attr("data-prototype"); // adresse d'envoi des données
			ConsoleResult("url d'envoi : ", url, true);
			$.ajax({
				type: "POST",
				url: url,
				data: $("#formvote").serialize(),
				error: function() {
					alert("Désolé, une erreur est survenue. Veuillez recommencer s.v.p.");
				}
			}).done( function(data) {
				ConsoleResult("Retour vote : ", data, true);
				retour = $.parseJSON(data);
				$(".new_avis_form_textarea_note").css("overflow", "hidden");
				$("#newAvisContainer").html('<div class="connectezvous"><p>'+retour.message+'</p></div>');
				majZone('Avis');
				majZone('Notation');
			});

			// window.location.replace($("#hiddenStuffs #actuelURL").val()); // recharge la page 
		}
	});

	////////////////////////////////////////////////////////
	// Vidéos en modale
	////////////////////////////////////////////////////////
	// $('body').on("click", ".modalevideo", function(event) {
	// 	event.preventDefault();
	// 	dat = $(this).attr("data-prototype").split("__");
	// 	link = dat[0];
	// 	title = dat[1];
	// 	$videowin = $('<div title="'+title+'"><iframe width="560" height="315" src="//www.youtube.com/embed/'+link+'" frameborder="0" allowfullscreen></iframe></div>');
	// 	$videowin.dialog({
	// 		modal: true,
	// 		width: 585,
	// 		height: 408,
	// 		show: {
	// 			effect: "fadeIn",
	// 			duration: 1000
	//   		},
	//   		hide: {
	// 			effect: "fadeOut",
	// 			duration: 1000
	//  		 },
	// 		buttons: {"Fermer": function(){$(this).dialog("close");}},
	// 		close: function() {
	// 			$(this).dialog("destroy").remove();
	// 		},
	// 		create: function() {
	// 			$(".ui-dialog-title", $(this)).hide();
	// 		}
	// 	});
	// 	return false;
	// });

	////////////////////////////////////////////////////////
	// Gestion du panier
	////////////////////////////////////////////////////////
	$("body").on('click', '.btn_panier', function(event) {
		param = $(this).attr('data-prototype').split("_");
		URL = $(this).attr('data-url');
		var data = new Object();
		// si quantité fournie en paramètre externe (QP avant le nombre) :
		if(param[1] == "QP") {
			param[1] = $("#QP"+param[2]).val();
		}
		// alert(param[0] + " " + param[1] + " article d'id = " + param[2] + "\nURL : " + URL);
		switch(param[0]) {
			case 'supprimer': // Supprimer un article du panier
				data["action"] = param[0];
				data["quantite"] = param[1];
				data["idarticle"] = param[2];
				break;
			case 'enlever': // Enlever x quantités d'un article
				data["action"] = param[0];
				data["quantite"] = param[1];
				data["idarticle"] = param[2];
				break;
			case 'ajouter': // Ajouter x quantités à un article
				data["action"] = param[0];
				data["quantite"] = param[1];
				data["idarticle"] = param[2];
				break;
			case 'viderPanier': // Supprimer tous les articles (vider le panier)
				data["action"] = param[0];
				break;
		}
		$.ajax({
			type: "POST",
			url: URL,
			data: data,
			error: function() {
				PopUpMessage("Erreur système", "Désolé, une erreur est survenue lors de la modification panier.", 0);
			}
		}).done( function(data) {
			retour = $.parseJSON(data);
			ConsoleResult("Retour panier : ", retour.message, true);
			// alert("Retour panier : " + retour.message);
			if(retour.result === true) {
				if(retour.message !== null) PopUpMessage("Modification de mon panier", retour.message, 3000);
			} else {
				if(retour.message !== null) PopUpMessage("Panier non modifié…", retour.message, 0);
			}
			majZone('tableauPanier');
			majZone('Iconepanier');
		});
	});

});












