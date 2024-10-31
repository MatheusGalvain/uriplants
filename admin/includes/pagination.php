 <?php if ($total_pages > 1): ?>
     <div class="pagination">
         <?php if ($page > 1): ?>
             <a href="?page=<?php echo $page - 1; ?>">&laquo; Anterior</a>
         <?php else: ?>
             <span class="disabled">&laquo; Anterior</span>
         <?php endif; ?>
         <?php
            $range = 2;
            for ($i = max(1, $page - $range); $i <= min($page + $range, $total_pages); $i++):
                if ($i == $page):
            ?>
                 <span class="active"><?php echo $i; ?></span>
             <?php else: ?>
                 <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
             <?php endif; ?>
         <?php endfor; ?>
         <?php if ($page < $total_pages): ?>
             <a href="?page=<?php echo $page + 1; ?>">Próxima &raquo;</a>
         <?php else: ?>
             <span class="disabled">Próxima &raquo;</span>
         <?php endif; ?>
     </div>
 <?php endif; ?>