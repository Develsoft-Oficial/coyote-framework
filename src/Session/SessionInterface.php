<?php

namespace Coyote\Session;

/**
 * Interface SessionInterface
 * 
 * Interface de compatibilidade para manter compatibilidade com código existente.
 * Esta interface será depreciada em favor da interface Store.
 * 
 * @deprecated Use \Coyote\Session\Store instead
 */
interface SessionInterface extends Store
{
    // Esta interface estende Store para manter compatibilidade
    // Métodos já definidos na interface Store
}