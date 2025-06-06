#if(${PARAM_DOC} == '' && ${THROWS_DOC} == '')
#else
/**
${PARAM_DOC}
${THROWS_DOC}
*/
#end
public function __construct(${PARAM_LIST}) {${BODY}}