/**
 * @author Terry Haire
 *         https://github.com/terry-haire
 *         https://linkedin.com/in/terry-haire
 *
 * January 2019
 */


class Patroon {
  /**
   * Patroon die de regex en de conditienaam bevat.
   * @param {string} type        Type van het element
   * @param {regex}  patroon     regex
   * @param {string} conditie    Naam van de conditie
   */
  constructor (type, patroon, conditie) {
    if (patroon === '') {
      if (type === 'email') {
        /**
         * Regex voor email adressen
         * Bron regex: https://stackoverflow.com/questions/46155/how-to-validate-an-email-address-in-javascript
         * Bron splitsing: https://stackoverflow.com/questions/12317049
         */
        var regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      } else {
        throw new Error('Error: patroon is leeg maar heeft geen speciaal type!')
      }
    } else {
      /**
       * Parse de regex van het JSON bestand.
       * Bron: https://stackoverflow.com/questions/874709/converting-user-input-string-to-regular-expression
       */
      var flags = patroon.replace(/.*\/([gimy]*)$/, '$1')
      var pattern = patroon.replace(new RegExp('^/(.*?)/' + flags + '$'), '$1')
      regex = new RegExp(pattern, flags)
    }

    this.patroon = regex
    this.conditie = conditie
  }
}

/**
 * De Form class voert de front-end form validatie uit. Het gebruikt de stijlen van het gebruikte template om de status
 * van de condities aan te tonen.
 *
 * Minimale lengte en maximale lengte zijn altijd inbegrepen. Deze hebben een standard conditie naam.
 * Als je een exacte lengte zoekt moet je min en max dezelfde waarde geven en een element met
 * id '[id]-status-lengte' toevoegen.
 * Voeg anders altijd de elementen '[id]-status-min-lengte' en '[id]-status-max-lengte' onder je inputs toe.
 * Gebruik verder voor het valideren van je forms alleen [JOUW-FORM-OBJECT.valideer()].
 * Voor nieuwe condities gebruik: [JOUW-FORM-OBJECT.nieuwPatroon(patroon,conditie)].
 */
class Form {
  /**
   * Verbind de class aan een element.
   * <tag id="[id]"></tag>
   * De form naam is in html te vinden in:
   * <form name="[form]"></form>
   * Noteer voor [min] en [max] dat forms altijd strings geven.
   * @param {string} id           Element id
   * @param {string} form         Form naam
   * @param {json}   json         JSON met condities (geverifieerd in de backend)
   * @param {string} classGoed   Style class als goed
   * @param {string} classInit   Style class als init
   * @param {string} classFout   Style class als fout
   */
  constructor (id, form, json, classGoed, classInit, classFout) {
    this.id = id
    this.form = form
    this.patronen = []
    this.status = false

    /** Haal de definities op. */
    var condities = JSON.parse(json)
    this.type = condities['type']
    this.required = condities['required']
    this.min = condities['min']
    this.max = condities['max']

    /** Initialiseer de patronen. */
    for (var i = 0; i < condities['patronen'].length; i++) {
      var patroon = condities['patronen'][i]
      this.patronen.push(new Patroon(this.type, patroon['patroon'], patroon['conditie']))
    }

    this.classGoed = classGoed
    this.classInit = classInit
    this.classFout = classFout

    /** Controlleer of het element bestaat. */
    if (!document.getElementById(id)) {
      throw new Error('Element ' + id + ' niet gevonden!!!')
    }
  }

  /**
   * Voeg een conditie toe.
   * @param {string} patroon  Regex van de conditie
   * @param {string} conditie Naam van de conditie
   */
  nieuwPatroon (patroon, conditie) {
    /** Controlleer of het patroon al bestaat. */
    for (var i = 0; i < this.patronen.length; i++) {
      if (patroon === this.patronen[i].patroon || conditie === this.patronen[i].conditie) {
        throw new Error('Error: patroon bestaat al!!!')
      }
    }

    this.patronen.push(new Patroon(patroon, conditie))
  }

  /**
   * Zet de stijl van de conditie afhankelijk van de status
   * @param {boolean} staat De nieuwe staat van de conditie goed/fout
   * @param {*}       conditie De naam van de conditie bijv: '-status-min-lengte'
   */
  zetStyle (staat, conditie) {
    /** Krijg het html element */
    var el = document.getElementById(this.id + conditie)
    if (!el) {
        console.log(this.id);
    }

    if (this.classGoed && this.classFout) {
      /** Verwijder de init class */
      el.classList.remove(this.classInit)

      /** Draai de classes om. */
      if (staat === -1) {
        el.classList.remove(this.classFout)
        el.classList.remove(this.classGoed)
        el.classList.add(this.classInit)
      } else if (staat) {
        el.classList.remove(this.classInit)
        el.classList.remove(this.classFout)
        el.classList.add(this.classGoed)
      } else {
        el.classList.remove(this.classInit)
        el.classList.remove(this.classGoed)
        el.classList.add(this.classFout)
      }
    } else if (this.classGoed || this.classFout) {
      throw new Error('Error: een van de classes is niet gedefinieerd')
    } else {
      /** Verstop het element (als de conditie goed is) of laat het zien. */
      if (staat) {
        el.style.display = 'none'
      } else {
        el.style.display = 'block'
      }
    }
  }

  /**
   * Valideer de form en zet alle condities naar de juiste style.
   */
  valideer () {
    /** Haal de input van de form op. */
    var input = document.forms[this.form][this.id]
    var str = input.value
    var len = str.length
    var result = true

    if (this.patronen.length === 0) {
      throw new Error('Error: geen patronen!!!')
    }

    result = this.valideerLengte(len)

    for (var i = 0; i < this.patronen.length; i++) {
      if (!this.valideerConditie(str, i)) {
        result = false
      }
    }

    if (len === 0) {
      if (this.required) {
        result = false
      } else {
        result = true
      }
    }

    this.status = result
    return result
  }

  /**
   * Vergelijk het patroon met de input.
   * @param {string}  str     Input
   * @param {Patroon} index   Index van het patroon
   */
  valideerConditie (str, index) {
    var result = false
    var patroon = this.patronen[index]
    var regex = patroon.patroon

    if (str.length === 0) {
      this.zetStyle(-1, patroon.conditie)
      return false
    }

    result = (str.search(regex) > -1)
    if (result) {
      this.zetStyle(true, patroon.conditie)
    } else {
      this.zetStyle(false, patroon.conditie)
    }

    return result
  }

  /**
   * Update de conditie met het aantal karakters in de input.
   * @param {int} len Aantal karakters.
   */
  updateTeller (len) {
    if (this.type === 'teller') {
      var el = document.getElementById(this.id + '-status-teller')
      el.innerHTML = len + '/' + this.max

      if (len === 0) {
        this.zetStyle(-1, '-status-teller')
      } else if (len < this.min || len > this.max) {
        this.zetStyle(0, '-status-teller')
      } else {
        this.zetStyle(1, '-status-teller')
      }
    }
  }

  /**
   * Valideer de lengte van de input.
   * @param {int} len Lengte van de input
   */
  valideerLengte (len) {
    this.updateTeller(len)

    if (this.min === this.max) {
      if (len === 0) {
        this.zetStyle(-1, '-status-exact')
        return false
      }

      if (len === this.min) {
        this.zetStyle(true, '-status-exact')
        return true
      }

      this.zetStyle(false, '-status-exact')
      return false
    }

    if (len < this.min && len !== 0) {
      this.zetStyle(false, '-status-min')
      /** Len is kleiner dan min dus ook dan max. */
      this.zetStyle(true, '-status-max')

      return false
    } else {
      if (len === 0) {
        this.zetStyle(-1, '-status-min')
        this.zetStyle(-1, '-status-max')
        return false
      }

      this.zetStyle(true, '-status-min')
      if (len > this.max) {
        this.zetStyle(false, '-status-max')
        return false
      } else {
        this.zetStyle(true, '-status-max')
      }
    }

    return true
  }
}

/**
 * Zet de gegeven knop knop aan of uit.
 *
 * Werkt alleen met submit knop met style submit-aan of submit-uit.
 * @param id id van de knop
 * @param staat true: aan, false: uit
 */
function schakelKnop (id, staat) {
  if (!staat) {
    document.getElementById(id).classList.remove('submit-aan')
    document.getElementById(id).classList.add('submit-uit')
    document.getElementById(id).disabled = true
  } else {
    document.getElementById(id).classList.remove('submit-uit')
    document.getElementById(id).classList.add('submit-aan')
    document.getElementById(id).disabled = false
  }
}

/**
 * Deze class bevat een collectie van forms.
 */
/* exported Forms */
class Forms {
  /**
   * Maak een nieuw Forms object.
   */
  constructor () {
    this.forms = []
    this.knoppen = []
  }

  /**
   * Link een knop aan de class.
   * @param {str} id
   */
  addKnop (id) {
    this.knoppen.push(id)
  }

  /**
   * Voeg een Form object toe aan de Forms class/
   * @param {Form} form
   */
  add (form) {
    this.forms.push(form)
  }

  /**
   * Valideer de Forms.
   */
  init () {
    for (var i = 0; i < this.forms.length; i++) {
      this.forms[i].valideer()
    }

    this.valideer()
  }

  /**
   * Zet de juiste styling van de condities en knoppen afhankelijk van de status van de forms.
   */
  valideer () {
    var i = 0

    if (this.forms.length === 0) {
      for (i = 0; i < this.knoppen.length; i++) {
        schakelKnop(this.knoppen[i], false)
      }
      return false
    }

    for (i = 0; i < this.forms.length; i++) {
      if (!this.forms[i].status) {
        for (var j = 0; j < this.knoppen.length; j++) {
          schakelKnop(this.knoppen[j], false)
        }
        return false
      }
    }

    for (i = 0; i < this.knoppen.length; i++) {
      schakelKnop(this.knoppen[i], true)
    }

    return true
  }
}

/**
 * Genegeerde unused vars.
 */
window.exported = function () { Form(); Forms() }
