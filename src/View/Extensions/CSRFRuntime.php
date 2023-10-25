<?php

declare(strict_types=1);

namespace Zaphyr\Framework\View\Extensions;

use Zaphyr\Session\Contracts\SessionInterface;
use Zaphyr\Utils\Form;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class CSRFRuntime
{
    /**
     * @param SessionInterface $session
     */
    public function __construct(protected SessionInterface $session)
    {
    }

    /**
     * @return string
     */
    public function csrfToken(): string
    {
        return $this->session->getToken() ?? '';
    }

    /**
     * @return string
     */
    public function csrfField(): string
    {
        return Form::hidden('_token', $this->csrfToken());
    }
}
