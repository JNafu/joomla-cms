/**
 * @version SVN: $Id: l0gvi3w.js 448 2011-07-18 03:19:08Z elkuku $
 * @package    L0gVi3w
 * @subpackage JavaScript
 * @author     Nikolai Plath {@link http://nik-it.de}
 * @author     Created on 17-Jul-2011
 * @license    GNU/GPL
 */

var pollRequest = new Request.JSON({
    method: 'post',
    url: 'index.php?option=com_l0gvi3w&task=pollLog&format=raw',
    initialDelay: 0,
    delay: 2000,
    limit: 15000,

    onRequest: function(){
       document.id('pollStatus').set('text', 'running...');
    },

    onSuccess: function(response){
       document.id('pollLog').set('html', response.text);
       document.id('pollStatus').set('text', 'waiting...');
    },

    onFailure: function(){
       document.id('pollStatus').set('text', 'Sorry, your request failed :(');
    }
});

function startPoll()
{
   pollRequest.startTimer();
}

function stopPoll()
{
   pollRequest.stopTimer();

   document.id('pollStatus').set('text', 'idle');
}

function changeState(id)
{
    var el = document.getElementById(id);

    el.style.display = (el.style.display != 'none' ? 'none' : 'block' )
}//function
